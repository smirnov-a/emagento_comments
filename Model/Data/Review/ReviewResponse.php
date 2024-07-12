<?php

namespace Emagento\Comments\Model\Data\Review;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Review\ReviewResponseInterface;

class ReviewResponse extends AbstractSimpleObject implements ReviewResponseInterface
{
    /**
     * Get Reviews
     *
     * @return \Emagento\Comments\Api\Data\Review\ReviewInterface[]
     */
    public function getReviews()
    {
        return $this->_get(self::REVIEWS);
    }

    /**
     * Set Reviews
     *
     * @param \Emagento\Comments\Api\Data\Review\ReviewInterface[] $value
     * @return \Emagento\Comments\Api\Data\Review\ReviewResponseInterface
     */
    public function setReviews($value)
    {
        return $this->setData(self::REVIEWS, $value);
    }

    /**
     * Get Ratings
     *
     * @return \Emagento\Comments\Api\Data\Rating\RatingInterface[]
     */
    public function getRatings()
    {
        return $this->_get(self::RATINGS);
    }

    /**
     * Set Ratings
     *
     * @param \Emagento\Comments\Api\Data\Rating\RatingInterface[] $value
     * @return \Emagento\Comments\Api\Data\Review\ReviewResponseInterface
     */
    public function setRatings($value)
    {
        return $this->setData(self::RATINGS, $value);
    }
}
