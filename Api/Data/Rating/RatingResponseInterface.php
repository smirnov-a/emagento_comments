<?php

namespace Emagento\Comments\Api\Data\Rating;

interface RatingResponseInterface
{
    public const RATINGS = 'ratings';

    /**
     * Get Ratings
     *
     * @return \Emagento\Comments\Api\Data\Rating\RatingInterface[]
     */
    public function getRatings();

    /**
     * Set Ratings
     *
     * @param \Emagento\Comments\Api\Data\Rating\RatingInterface[] $value
     * @return RatingResponseInterface
     */
    public function setRatings($value): RatingResponseInterface;
}
