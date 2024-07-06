<?php

namespace Emagento\Comments\Model;

use Magento\Review\Model\Rating as MagentoRating;

class Rating extends MagentoRating
{
    /**
     * Set Rating ID
     *
     * @param string|null $value
     * @return $this
     */
    public function setRatingId($value)
    {
        $this->setData('rating_id', $value);
        return $this;
    }

    /**
     * Set Review Id
     *
     * @param string|null $value
     * @return $this
     */
    public function setReviewId($value)
    {
        $this->setData('review_id', $value);
        return $this;
    }
}
