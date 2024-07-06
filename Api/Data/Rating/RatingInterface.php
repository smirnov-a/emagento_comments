<?php

namespace Emagento\Comments\Api\Data\Rating;

interface RatingInterface
{
    public const RATING_ID = 'rating_id';
    public const ENTITY_ID = 'entity_id';
    public const RATING_CODE = 'rating_code';
    public const OPTIONS = 'options';

    /**
     * Get Rating Id
     *
     * @return string|null
     */
    public function getRatingId(): ?string;

    /**
     * Set Rating Id
     *
     * @param string|null $value
     * @return RatingInterface
     */
    public function setRatingId(?string $value): RatingInterface;

    /**
     * Get Entity ID
     *
     * @return string|null
     */
    public function getEntityId(): ?string;

    /**
     * Set Entity ID
     *
     * @param string|null $value
     * @return RatingInterface
     */
    public function setEntityId(?string $value): RatingInterface;

    /**
     * Get Rating Code
     *
     * @return string|null
     */
    public function getRatingCode(): ?string;

    /**
     * Set Rating Code
     *
     * @param string|null $value
     * @return RatingInterface
     */
    public function setRatingCode(?string $value): RatingInterface;

    /**
     * Get Options
     *
     * @return \Emagento\Comments\Api\Data\Rating\OptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set Options
     *
     * @param \Emagento\Comments\Api\Data\Rating\OptionInterface[]|null $value
     * @return RatingInterface
     */
    public function setOptions($value): RatingInterface;
}
