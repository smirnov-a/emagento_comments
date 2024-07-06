<?php

namespace Emagento\Comments\Plugin;

use Emagento\Comments\Api\ReviewRepositoryInterface;
use Psr\Log\LoggerInterface;
use Emagento\Comments\Helper\Data as Helper;

class Review
{
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var ReviewRepositoryInterface */
    private ReviewRepositoryInterface $reviewRepository;
    /** @var Helper */
    private Helper $helper;

    /**
     * @param LoggerInterface $logger
     * @param ReviewRepositoryInterface $reviewRepository
     * @param Helper $helper
     */
    public function __construct(
        LoggerInterface $logger,
        ReviewRepositoryInterface $reviewRepository,
        Helper $helper,
    ) {
        $this->logger = $logger;
        $this->reviewRepository = $reviewRepository;
        $this->helper = $helper;
    }

    /**
     * Work with Store Review before Save
     *
     * @param \Magento\Review\Model\Review $subject
     */
    public function beforeSave(\Magento\Review\Model\Review $subject)
    {
        if ($subject->getEntityId() != $this->helper->getStoreReviewEntityId()) {
            return;
        }
        try {
            $path = $subject->getId();
            $level = 1;
            if ($subject->getParentId()) {
                $parent = $this->reviewRepository->getById($subject->getParentId());
                if ($parent->getId()) {
                    $level = $parent->getLevel() + 1;
                    $path = $parent->getPath() . '/' . $path;
                }
            }
            $this->logger->info('Store Comment. Path: ' . $path . '; level: ' . $level);

            $subject
                ->setPath($path)
                ->setLevel($level);
        } catch (\Throwable $e) { // phpcs:ignore
        }
    }
}
