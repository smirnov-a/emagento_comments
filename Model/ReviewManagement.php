<?php

namespace Emagento\Comments\Model;

use Emagento\Comments\Api\ReviewManagementInterface;
use Emagento\Comments\Api\Data\Review\ReviewResponseInterface;
use Emagento\Comments\Api\Data\Review\ReviewResponseInterfaceFactory;
use Emagento\Comments\Api\Data\Rating\RatingResponseInterface;
use Emagento\Comments\Api\Data\Rating\RatingResponseInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Emagento\Comments\Model\DataProvider\Review as ReviewDataProvider;
use Emagento\Comments\Model\DataProvider\Rating as RatingDataProvider;
use Emagento\Comments\Helper\Constants;
use Emagento\Comments\Api\ReviewRepositoryInterface;

class ReviewManagement implements ReviewManagementInterface
{
    /** @var ReviewResponseInterfaceFactory */
    private ReviewResponseInterfaceFactory $reviewResponseInterfaceFactory;
    /** @var RatingResponseInterfaceFactory */
    private RatingResponseInterfaceFactory $ratingResponseInterfaceFactory;
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var Request */
    private Request $request;
    /** @var SerializerInterface */
    private SerializerInterface $serializer;
    /** @var ReviewDataProvider */
    private ReviewDataProvider $reviewDataProvider;
    /** @var RatingDataProvider */
    private RatingDataProvider $ratingDataProvider;
    /** @var ReviewRepositoryInterface */
    private ReviewRepositoryInterface $reviewRepository;

    /**
     * @param ReviewResponseInterfaceFactory $reviewResponseInterfaceFactory
     * @param RatingResponseInterfaceFactory $ratingResponseInterfaceFactory
     * @param LoggerInterface $logger
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ReviewDataProvider $reviewDataProvider
     * @param RatingDataProvider $ratingDataProvider
     * @param ReviewRepositoryInterface $reviewRepository
     */
    public function __construct(
        ReviewResponseInterfaceFactory $reviewResponseInterfaceFactory,
        RatingResponseInterfaceFactory $ratingResponseInterfaceFactory,
        LoggerInterface $logger,
        Request $request,
        SerializerInterface $serializer,
        ReviewDataProvider $reviewDataProvider,
        RatingDataProvider $ratingDataProvider,
        ReviewRepositoryInterface $reviewRepository,
    ) {
        $this->reviewResponseInterfaceFactory = $reviewResponseInterfaceFactory;
        $this->ratingResponseInterfaceFactory = $ratingResponseInterfaceFactory;
        $this->logger = $logger;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->reviewDataProvider = $reviewDataProvider;
        $this->ratingDataProvider = $ratingDataProvider;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Get Review List
     *
     * @return ReviewResponseInterface
     */
    public function getReviewList(): ReviewResponseInterface
    {
        $this->logger->info($this->request->getPathInfo());
        $this->logger->info('Request: ' . $this->serializer->serialize($this->request->getRequestData()));

        $urlParams = $this->request->getParams();
        $page = $urlParams['page'] ?? 1;
        $limit = $urlParams['limit'] ?? Constants::LIMIT;
        $result = $this->reviewResponseInterfaceFactory->create();
        try {
            $result
                ->setRatings($this->ratingDataProvider->getRatings())
                ->setReviews($this->reviewDataProvider->getReviews($page, $limit))
            ;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->logger->info('Response: ' . $this->serializer->serialize($result->__toArray()));
        $this->logger->info('');

        return $result;
    }

    /**
     * Get Rating List
     *
     * @return RatingResponseInterface
     */
    public function getRatingList(): RatingResponseInterface
    {
        $result = $this->ratingResponseInterfaceFactory->create();
        $result->setRatings($this->ratingDataProvider->getRatings());

        return $result;
    }

    /**
     * Review delete
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $review = $this->reviewRepository->getById($id);
            return $this->reviewRepository->delete($review);
        } catch (\Exception $e) {
            return false;
        }
    }
}
