<?php

namespace Emagento\Comments\Model\Data\Rating;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Rating\RatingResponseInterface;

class RatingResponse extends AbstractSimpleObject implements RatingResponseInterface
{
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
     * @return \Emagento\Comments\Api\Data\Rating\RatingResponseInterface
     */
    public function setRatings($value): RatingResponseInterface
    {
        return $this->setData(self::RATINGS, $value);
    }
}
