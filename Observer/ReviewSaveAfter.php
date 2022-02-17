<?php

namespace Emagento\Comments\Observer;

use Psr\Log\LoggerInterface;

class ReviewSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Emagento\Comments\Model\ResourceModel\Review
     */
    protected $_reviewResource;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Emagento\Comments\Model\ResourceModel\Review $reviewResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Emagento\Comments\Model\ResourceModel\Review $reviewResource,
        LoggerInterface $logger
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_reviewResource = $reviewResource;
        $this->_logger = $logger;
    }

    /**
     * Observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        if ($dataObject->getPath()
            || $dataObject->getEntityId() != \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE
        ) {
            return $this;
        }

        // update 'path' and 'level'
        $path  = $dataObject->getId();
        $level = 1;
        // try to get form parent
        if ($dataObject->getParentId()) {
            $parent = $this->_reviewFactory->create()->load($dataObject->getParentId());
            if ($parent->getId()) {
                $level = $parent->getLevel() + 1;
                $path  = $parent->getPath() . '/' . $path;
            }
        }
        $data = ['path' => $path, 'level' => $level];
        $this->_reviewResource->updatePathAndLevel($dataObject->getId(), $data);

        return $this;
    }
}
