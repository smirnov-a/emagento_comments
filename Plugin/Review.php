<?php

namespace Emagento\Comments\Plugin;

class Review
{
    private $logger;
    private $reviewFactory;

    /**
     * Review constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Review\Model\ReviewFactory $factory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Review\Model\ReviewFactory $factory
    ) {
        $this->logger = $logger;
        $this->reviewFactory = $factory;
    }

    /**
     * Work with review before save
     *
     * @param \Magento\Review\Model\Review $subject
     */
    public function beforeSave(\Magento\Review\Model\Review $subject)
    {
        if ($subject->getEntityId() != \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE) {
            return;
        }

        $path  = $subject->getId();
        $level = 1;
        if ($subject->getParentId()) {
            $parent = $this->reviewFactory->create()->load($subject->getParentId());
            if ($parent) {
                $level = $parent->getLevel() + 1;
                $path  = $parent->getPath() . '/' . $path;
            }
        }
        $this->logger->info('Store Comment. Path: '.$path.'; level: '.$level);

        $subject
            ->setPath($path)
            ->setLevel($level);
    }
}
