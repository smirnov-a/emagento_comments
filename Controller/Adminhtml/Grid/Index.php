<?php

namespace Local\Comments\Controller\Adminhtml\Grid;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    const ACL_RESOURCE = 'Local_Comments::comments_list';   // из acl.xml
    const MENU_ITEM = 'Local_Comments::comments_list';      // из menu.xml

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Local_Comments::comments_list';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

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
    /* это в Magento\Backend\App\Magento\Backend\App
    protected function _isAllowed()
    {
        $result = parent::_isAllowed();
        $result = $result && $this->_authorization->isAllowed(self::ACL_RESOURCE);
        return $result;
    }
    */

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /*
        if ($this->getRequest()->getParam('ajax')) {
            /* * @var \Magento\Backend\Model\View\Result\Forward $resultForward * /
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('reviewGrid');
            return $resultForward;
        }
        */
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        //$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::MENU_ITEM);
        $resultPage->addBreadcrumb(__('Local Comments'), __('Local Comments'));
        $resultPage->addBreadcrumb(__('Local Comments'), __('Local Comments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Store comments'));

        return $resultPage;
    }
}
