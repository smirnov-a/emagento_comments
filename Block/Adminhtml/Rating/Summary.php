<?php

namespace Emagento\Comments\Block\Adminhtml\Rating;

/**
 * Adminhtml summary rating stars
 */
class Summary extends \Magento\Review\Block\Adminhtml\Rating\Summary
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $votesFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $votesFactory, $ratingFactory, $registry);
    }

    /**
     * Get collection of ratings
     *
     * @return RatingCollection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            $ratingCollection = $this->_votesFactory->create()->setReviewFilter(
                $this->getReviewId()
            )->addRatingInfo()->load();
            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }
        //echo $this->getRatingCollection()->getSelect(); exit;
        return $this->getRatingCollection();
    }

    /**
     * Get rating summary
     *
     * @return string
     */
    public function getRatingSummary()
    {
        if (!$this->getRatingSummaryCache()) {
            $this->setRatingSummaryCache($this->_ratingFactory->create()->getReviewSummary($this->getReviewId()));
        }

        return $this->getRatingSummaryCache();
    }
}
