<?php

namespace Emagento\Comments\Controller\Adminhtml\Comment;

use Emagento\Comments\Api\ReviewRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\Registry;
use Magento\Review\Model\RatingFactory;

class Edit extends ProductController
{
    public const ADMIN_RESOURCE = 'Emagento_Comments::store_reviews';

    /** @var ReviewRepositoryInterface  */
    private ReviewRepositoryInterface $reviewRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param ReviewRepositoryInterface $reviewRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        ReviewRepositoryInterface $reviewRepository,
    ) {
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Execute
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Review'));

        return $resultPage;
    }
}
