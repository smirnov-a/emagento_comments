<?php

namespace Emagento\Comments\Api\Data\Review;

interface ReviewResponseInterface
{
    public const REVIEWS = 'reviews';
    public const RATINGS = 'ratings';

    /**
     * Get Reviews
     *
     * @return \Emagento\Comments\Model\Data\Review\ReviewResults
     */
    public function getReviews();

    /**
     * Set Reviews
     *
     * @param \Emagento\Comments\Model\Data\Review\ReviewResults $value
     * @return ReviewResponseInterface
     */
    public function setReviews($value);

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
     * @return ReviewResponseInterface
     */
    public function setRatings($value);
}
