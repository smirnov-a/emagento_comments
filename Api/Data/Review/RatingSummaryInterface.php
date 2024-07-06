<?php

namespace Emagento\Comments\Api\Data\Review;

interface RatingSummaryInterface
{
    public const PERCENT = 'percent';
    public const VALUE = 'value';
    public const DESCRIPTION = 'description';
    public const RATING_ID = 'rating_id';

    /**
     * Get Percent
     *
     * @return string|null
     */
    public function getPercent(): ?string;

    /**
     * Set Percent
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setPercent(?string $value): RatingSummaryInterface;

    /**
     * Get Value
     *
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * Set Value
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setValue(?string $value): RatingSummaryInterface;

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Set Description
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setDescription(?string $value): RatingSummaryInterface;

    /**
     * Get Rating ID
     *
     * @return string|null
     */
    public function getRatingId(): ?string;

    /**
     * Set Rating ID
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setRatingId(?string $value): RatingSummaryInterface;
}
