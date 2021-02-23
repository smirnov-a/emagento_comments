<?php

namespace Emagento\Comments\Model;

use Magento\Review\Model\Rating as MagentoRating;

class Rating extends MagentoRating
{
    /**
     * Load by multiple attributes
     *
     * @param $attributes
     * @return $this
     * @throws \Exception
     */
    public function loadByAttributes($attributes)
    {
        $this->_getResource()->loadByAttributes($this, $attributes);
        return $this;
    }

    /**
     * Это для прохождения unit-теста
     * @param mixed $value
     * @return $this
     */
    public function setRatingId($value)
    {
        $this->setData('rating_id', $value);
        return $this;
    }

    public function setReviewId($value)
    {
        $this->setData('review_id', $value);
        return $this;
    }
}
