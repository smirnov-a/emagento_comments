<?php

namespace Emagento\Comments\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ACL_RESOURCE = 'Emagento_Comments::store_reviews';
    public const MENU_ITEM    = 'Emagento_Comments::comments_list';
    public const ADMIN_RESOURCE = 'Emagento_Comments::store_reviews';

    /** @var PageFactory */
    protected PageFactory $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::MENU_ITEM);
        $resultPage->addBreadcrumb(__('Emagento Comments'), __('Emagento Comments'));
        $resultPage->addBreadcrumb(__('Emagento Comments'), __('Emagento Comments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Store Reviews'));

        return $resultPage;
    }
}
