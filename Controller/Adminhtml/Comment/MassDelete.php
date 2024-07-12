<?php

namespace Emagento\Comments\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\App\Action\Context;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Emagento\Comments\Api\ReviewManagementInterface;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'Emagento_Comments::store_reviews';

    /** @var Filter */
    private $filter;
    /** @var CollectionFactory */
    private $collectionFactory;
    /** @var ReviewManagementInterface */
    private $reviewManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ReviewManagementInterface|null $reviewManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ReviewManagementInterface $reviewManagement = null
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->reviewManagement = $reviewManagement ?: ObjectManager::getInstance()->get(
            ReviewManagementInterface::class
        );
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $this->fixWhereConditionInFilter($collection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $review) {
            $this->reviewManagement->delete($review->getId());
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Fix where condition: review_id => main_table.review_id
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @return void
     */
    private function fixWhereConditionInFilter($collection)
    {
        $select = $collection->getSelect();
        $wherePart = $select->getPart(\Magento\Framework\DB\Select::WHERE);
        if (!$wherePart) {
            return;
        }

        $fieldName = $collection->getResource()->getIdFieldName();
        foreach ($wherePart as &$condition) {
            if (str_contains($condition, $fieldName)) {
                $condition = str_replace("`{$fieldName}`", "`main_table`.`{$fieldName}`", $condition);
            }
        }
        $select->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
    }
}
