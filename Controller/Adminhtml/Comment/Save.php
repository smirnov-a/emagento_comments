<?php

namespace Emagento\Comments\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Review\Model\Rating\Option\Vote;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Backend\App\Action\Context;
use Emagento\Comments\Api\ReviewRepositoryInterface;
use \Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Review::pending';

    /** @var ReviewFactory */
    private ReviewFactory $reviewFactory;
    /** @var RatingFactory */
    private RatingFactory $ratingFactory;
    /** @var ReviewRepositoryInterface */
    private ReviewRepositoryInterface $reviewRepository;
    /** @var CollectionFactory */
    private CollectionFactory $voteCollectionFactory;
    /** @var JsonFactory */
    private JsonFactory $jsonFactory;
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param LoggerInterface $logger
     * @param CollectionFactory $voteCollectionFactory
     * @param ReviewRepositoryInterface $reviewRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        LoggerInterface $logger,
        CollectionFactory $voteCollectionFactory,
        ReviewRepositoryInterface $reviewRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->logger = $logger;
        $this->reviewRepository = $reviewRepository;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Execute
     *
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->jsonFactory->create();

        $reviewId = $this->getRequest()->getParam('review_id', false);
        $postData = $this->getRequest()->getPostValue();
        if (!$postData) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('No data to save.')
            ]);
        }

        $success = false;
        try {
            $review = $this->reviewRepository->getById($reviewId);
            $review->addData($postData);
            $this->reviewRepository->save($review);
            $this->processRatingData($review);

            $success = true;
            $message = __('Review and ratings have been saved successfully.');

        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $message = __('Error while saving data: %1', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $message = __('Something went wrong while saving this review.');
        }

        return $resultJson->setData([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Processing Data
     *
     * @param Review $review
     * @return void
     */
    private function processRatingData(Review $review): void
    {
        /** @var Vote $votes */
        $votes = $this->voteCollectionFactory->create()
            ->setReviewFilter($review->getId())
            ->addOptionInfo()
            ->load()
            ->addRatingOptions();

        $ratingsData = $this->getRequest()->getParam('ratings', []);
        foreach ($ratingsData as $ratingData) {
            $optionId = $ratingData['option_id'] ?? null;
            if (!$optionId) {
                continue;
            }
            $ratingId = (int) $ratingData['rating_id'];
            if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                $this->ratingFactory->create()
                    ->setVoteId($vote->getId())
                    ->setReviewId($review->getId())
                    ->updateOptionVote($optionId);
            } else {
                $this->ratingFactory->create()
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->addOptionVote($optionId, $review->getEntityPkValue());
            }
        }

        $review->aggregate();
    }
}
