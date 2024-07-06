<?php

namespace Emagento\Comments\Api\Data\Review;

use Emagento\Comments\Api\ReviewInterface as BaseReviewInterface;

interface ReviewInterface extends BaseReviewInterface
{
    public const RATING_SUMMARY = 'rating_summary';
    public const REPLY_DATA = 'reply_data';
    public const LOG_IMAGE = 'logo_image';

    /**
     * Get Rating Votes
     *
     * @return \Magento\Framework\DataObject
     */
    public function getRatingVotes();

    /**
     * Set Rating Votes
     *
     * @param \Magento\Framework\DataObject|null $value
     * @return ReviewInterface
     */
    public function setRatingVotes($value): ReviewInterface;

    /**
     * Get Rating Summary
     *
     * @return \Emagento\Comments\Api\Data\Review\RatingSummaryInterface|null
     */
    public function getRatingSummary();

    /**
     * Set Rating Summary
     *
     * @param \Emagento\Comments\Api\Data\Review\RatingSummaryInterface|null $value
     *
     * @return ReviewInterface
     */
    public function setRatingSummary($value): ReviewInterface;

    /**
     * Get Reply Data
     *
     * @return \Emagento\Comments\Api\Data\Review\ReplyDataInterface|null
     */
    public function getReplyData();

    /**
     * Set Reply Data
     *
     * @param \Emagento\Comments\Api\Data\Review\ReplyDataInterface|null $value
     * @return ReviewInterface
     */
    public function setReplyData($value): ReviewInterface;

    /**
     * Get Logo Image Path
     *
     * @return string|null
     */
    public function getLogoImage(): ?string;

    /**
     * Set Logo Image Path
     *
     * @param string|null $value
     *
     * @return ReviewInterface
     */
    public function setLogoImage(?string $value): ReviewInterface;
}
