<?php

namespace Local\Comments\Observer;

use Psr\Log\LoggerInterface;

class ReviewSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Local\Comments\Model\ResourceModel\Review
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
     * @param \Local\Comments\Model\ResourceModel\Review $reviewResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Local\Comments\Model\ResourceModel\Review $reviewResource,
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
        //$this->_logger->info('Starting review observer...');
        $dataObject = $observer->getEvent()->getDataObject();
        // после сохранения отзыва нужно проверить его тип entity_id
        // если \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE, то прописать/обновить path и level
        // работать если еще не заполнено (т.е. сработает при добавлении отзыва)
        if (!$dataObject->getPath() &&
            $dataObject->getEntityId() == \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE) {
            // обновить поле path и level прямым запросом
            $path = $dataObject->getId();
            $level = 1;
            // если у отзыва есть родитель, то взять с него path и построить новый
            if ($dataObject->getParentId()) {
                $parent = $this->_reviewFactory->create()->load($dataObject->getParentId());
                if ($parent->getId()) {
                    // взять level и path
                    $level = $parent->getLevel() + 1;           // 4
                    $path = $parent->getPath() . '/' . $path;   // '3/4/5/6'
                }
            }
            $data = ['path' => $path, 'level' => $level];
            $this->_reviewResource->updatePathAndLevel($dataObject->getId(), $data);
            //$this->_logger->info('Store Comment. id: '.$dataObject->getId().'; Path: '.$path.'; level: '.$level);
        }

        return $this;
    }
}
