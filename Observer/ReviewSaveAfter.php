<?php

namespace Emagento\Comments\Observer;

use Emagento\Comments\Api\ReviewRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Emagento\Comments\Helper\Data as EmagentoHelper;

class ReviewSaveAfter implements ObserverInterface
{
    /** @var ReviewRepositoryInterface */
    private ReviewRepositoryInterface $reviewRepository;
    /** @var ReviewFactory */
    private ReviewFactory $reviewFactory;
    /** @var EmagentoHelper */
    private EmagentoHelper $helper;

    /**
     * @param ReviewRepositoryInterface $reviewRepository
     * @param ReviewFactory $reviewFactory
     * @param EmagentoHelper $helper
     */
    public function __construct(
        ReviewRepositoryInterface $reviewRepository,
        ReviewFactory $reviewFactory,
        EmagentoHelper $helper
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        if ($dataObject->getPath()
            || !$this->isStoreReviewEntityId($dataObject)
        ) {
            return;
        }

        $this->updateLevelAndPath($dataObject);
    }

    /**
     * Update Review Levele and Path
     *
     * @param DataObject $dataObject
     * @return void
     */
    private function updateLevelAndPath(DataObject $dataObject)
    {
        $reviewId  = (int) $dataObject->getId();
        $path = $reviewId;
        $level = 1;
        if ($parentId = $dataObject->getParentId()) {
            $parentReview = $this->getParentReview($parentId);
            if ($parentReview->getId()) {
                $level = $parentReview->getLevel() + 1;
                $path  = $parentReview->getPath() . '/' . $reviewId;
            }
        }
        $data = ['path' => $path, 'level' => $level];
        $this->reviewRepository->updatePathAndLevel($reviewId, $data);
    }

    /**
     * Get Parent Review by ID
     *
     * @param int $parentId
     * @return Review
     */
    private function getParentReview(int $parentId): Review
    {
        try {
            $review = $this->reviewRepository->getById($parentId);
        } catch (\Exception $e) {
            $review = $this->reviewFactory->create();
        }
        return $review;
    }

    /**
     * Check if Entity ID is Store Review
     *
     * @param DataObject $dataObject
     * @return bool
     */
    private function isStoreReviewEntityId(DataObject $dataObject): bool
    {
        return $dataObject->getEntityId() == $this->helper->getStoreReviewEntityId();
    }
}
