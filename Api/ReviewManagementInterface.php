<?php

namespace Emagento\Comments\Api;

use Emagento\Comments\Api\Data\Review\ReviewResponseInterface;
use Emagento\Comments\Api\Data\Rating\RatingResponseInterface;

interface ReviewManagementInterface
{
    /**
     * Get Review List
     *
     * @return ReviewResponseInterface
     */
    public function getReviewList(): ReviewResponseInterface;

    /**
     * Get Rating List
     *
     * @return RatingResponseInterface
     */
    public function getRatingList(): RatingResponseInterface;

    /**
     * Delete specified review
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
