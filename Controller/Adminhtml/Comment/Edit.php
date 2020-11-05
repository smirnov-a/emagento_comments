<?php

namespace Local\Comments\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

class Edit extends ProductController implements HttpGetActionInterface
{
    /**
     * @var Review
     */
    private $review;

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Review'));
        //$resultPage->addContent($resultPage->getLayout()->createBlock(\Magento\Review\Block\Adminhtml\Edit::class));
        $resultPage->addContent($resultPage->getLayout()->createBlock(\Local\Comments\Block\Adminhtml\Edit::class));
        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        if (parent::_isAllowed()) {
            return true;
        }

        if (!$this->_authorization->isAllowed('Local_Comments::local_comments')) {
            return  false;
        }

        return true;
    }

    /**
     * Returns requested model.
     *
     * @return Review
     */
    private function getModel(): Review
    {
        if ($this->review === null) {
            $this->review = $this->reviewFactory->create()
                ->load($this->getRequest()->getParam('id', false));
        }

        return $this->review;
    }
}
