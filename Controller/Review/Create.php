<?php

namespace Emagento\Comments\Controller\Review;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\Json;
use Emagento\Comments\Helper\Data as Helper;
use Emagento\Comments\Helper\Constants;
use Emagento\Comments\Api\ReviewRepositoryInterface;

class Create implements HttpPostActionInterface
{
    /** @var JsonFactory */
    private JsonFactory $resultJsonFactory;
    /** @var RequestInterface */
    private RequestInterface $request;
    /** @var Helper */
    private Helper $helper;
    /** @var Validator */
    private Validator $formKeyValidator;
    /** @var RatingFactory */
    private RatingFactory $ratingFactory;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var Session */
    private Session $customerSession;
    /** @var ReviewRepositoryInterface */
    private ReviewRepositoryInterface $reviewRepository;

    /**
     * @param RequestInterface $request
     * @param Helper $helper
     * @param JsonFactory $resultJsonFactory
     * @param Validator $formKeyValidator
     * @param RatingFactory $ratingFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Session $session
     * @param ReviewRepositoryInterface $reviewRepository
     */
    public function __construct(
        RequestInterface $request,
        Helper $helper,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        RatingFactory $ratingFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Session $session,
        ReviewRepositoryInterface $reviewRepository,
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->ratingFactory = $ratingFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerSession = $session;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Execute
     *
     * @return Json
     * @throws \Magento\Framework\Validator\ValidateException
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->isRequestValid()) {
            return $resultJson->setData([
                'error' => __('Request error')
            ]);
        }
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->logger->error('formKeyValidator error');
            return $resultJson->setData([
                'error' => __('Form error. Please try again later')
            ]);
        }

        $data = $this->request->getPostValue();
        $review = $this->reviewRepository->getFactory()->create();
        $review->addData($data);
        $review->unsetData('review_id')
            ->setTitle(Constants::TITLE_DEFAULT);
        $validate = $review->validate();
        if ($validate !== true) {
            return $this->getErrorResultJson($validate, $resultJson);
        }

        $productId = 0;
        $customerId = $this->customerSession->getCustomerId();
        try {
            $review->setEntityPkValue($productId)
                ->setEntityId($this->helper->getStoreReviewEntityId())
                ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                ->setCustomerId($customerId)
                ->setNickname(trim($data['nickname']))
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setStores([$this->storeManager->getStore()->getId()])
            ;
            $this->reviewRepository->save($review);
            $this->processRatingData($review->getId(), $customerId, $productId);
            $review->aggregate();

            return $resultJson->setData([
                'message' => __('Thank you. Your review is awaiting moderation')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error append review: ' . $e->getMessage());
            return $resultJson->setData([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process Rating Data
     *
     * @param int $reviewId
     * @param int $customerId
     * @param int $productId
     * @return void
     */
    private function processRatingData($reviewId, $customerId, $productId)
    {
        $rating = $this->request->getPost('ratings', []);
        foreach ($rating as $ratingId => $optionId) {
            $this->ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($reviewId)
                ->setCustomerId($customerId)
                ->addOptionVote($optionId, $productId);
        }
    }

    /**
     * Check if Request is valid
     *
     * @return bool
     */
    private function isRequestValid(): bool
    {
        return $this->helper->isEnabled(Constants::TYPE_LOCAL)
            && (
                $this->customerSession->isLoggedIn()
                || $this->helper->isGuestAllowReviews()
            );
    }

    /**
     * Get Json Error Result
     *
     * @param array|string $validate
     * @param Json $resultJson
     * @return Json
     */
    private function getErrorResultJson($validate, Json $resultJson): Json
    {
        $msg = '';
        if (is_array($validate)) {
            foreach ($validate as $errorMsg) {
                $msg = $errorMsg;
                break;
            }
        } else {
            $msg = __('Please try again later');
        }
        $this->logger->error($msg);

        return $resultJson->setData([
            'error' => $msg
        ]);
    }
}
