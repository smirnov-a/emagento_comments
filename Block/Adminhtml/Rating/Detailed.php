<?php

namespace Local\Comments\Block\Adminhtml\Rating;

/**
 * Adminhtml detailed rating stars
 */
class Detailed extends \Magento\Review\Block\Adminhtml\Rating\Detailed
{
    /**
     * Rating detail template name
     *
     * @var string
     *
    protected $_template = 'Magento_Review::rating/detailed.phtml';
    */

    /**
     * Get collection of ratings
     *
     * @return RatingCollection
     */
    public function getRating()
    {
        if (!$this->getRatingCollection()) {
            if ($this->_coreRegistry->registry('review_data')) {
                $stores = $this->_coreRegistry->registry('review_data')->getStores();

                $stores = array_diff($stores, [0]);

                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'store'
                )->setStoreFilter(
                    $stores
                )->setActiveFilter(
                    true
                )->setPositionOrder()->load()->addOptionToItems();

                $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                    $this->getReviewId()
                )->addOptionInfo()->load()->addRatingOptions();
            } elseif (!$this->getIsIndependentMode()) {
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    null
                )->setPositionOrder()->load()->addOptionToItems();
            } else {
                $stores = $this->getRequest()->getParam('select_stores') ?: $this->getRequest()->getParam('stores');
                $ratingCollection = $this->_ratingsFactory->create()->addEntityFilter(
                    'product'
                )->setStoreFilter(
                    $stores
                )->setPositionOrder()->load()->addOptionToItems();
                if ((int)$this->getRequest()->getParam('id')) {
                    $this->_voteCollection = $this->_votesFactory->create()->setReviewFilter(
                        (int)$this->getRequest()->getParam('id')
                    )->addOptionInfo()->load()->addRatingOptions();
                }
            }
            $this->setRatingCollection($ratingCollection->getSize() ? $ratingCollection : false);
        }

        return $this->getRatingCollection();
    }
}