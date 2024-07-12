<?php

namespace Emagento\Comments\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassChangeStatus extends Action
{
    public const ADMIN_RESOURCE = 'Emagento_Comments::store_reviews';

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $statusId = $this->getRequest()->getParam('status');
        $ids = $this->getRequest()->getParam('selected');

        if ($statusId !== null && !empty($ids)) {
            try {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('main_table.review_id', ['in' => $ids]);
                foreach ($collection as $item) {
                    $item->setStatusId($statusId)
                        ->save();
                }
                $this->messageManager->addSuccessMessage(__('Status has been changed.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid status or no items selected.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
