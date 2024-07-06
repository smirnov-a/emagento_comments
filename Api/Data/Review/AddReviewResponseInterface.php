<?php

namespace Emagento\Comments\Api\Data\Review;

interface AddReviewResponseInterface
{
    /**
     * Get Success
     *
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * Set Success
     *
     * @param bool $value
     * @return AddReviewResponseInterface
     */
    public function setSuccess(bool $value): AddReviewResponseInterface;
}
