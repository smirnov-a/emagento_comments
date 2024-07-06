<?php

namespace Emagento\Comments\Block\Adminhtml\Rating;

use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

class Summary extends \Magento\Review\Block\Adminhtml\Rating\Summary
{
    /**
     * Get collection of ratings
     *
     * @return RatingCollection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            $ratingCollection = $this->_votesFactory->create()
                ->setReviewFilter($this->getReviewId())
                ->addRatingInfo()
                ->load();
            $this->setRatingCollection($ratingCollection->getSize()
                ? $ratingCollection
                : false);
        }

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
            $this->setRatingSummaryCache(
                $this->_ratingFactory->create()->getReviewSummary($this->getReviewId())); // phpcs:ignore
        }

        return $this->getRatingSummaryCache();
    }
}
