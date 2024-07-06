<?php

namespace Emagento\Comments\Model\Data\Review;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Review\RatingSummaryInterface;

class RatingSummary extends AbstractSimpleObject implements RatingSummaryInterface
{
    /**
     * Get Percent
     *
     * @return string|null
     */
    public function getPercent(): ?string
    {
        return $this->_get(self::PERCENT);
    }

    /**
     * Set Percent
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setPercent(?string $value): RatingSummaryInterface
    {
        return $this->setData(self::PERCENT, $value);
    }

    /**
     * Get Value
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Set Value
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setValue(?string $value): RatingSummaryInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setDescription(?string $value): RatingSummaryInterface
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * Get Rating ID
     *
     * @return string|null
     */
    public function getRatingId(): ?string
    {
        return $this->_get(self::RATING_ID);
    }

    /**
     * Set Rating ID
     *
     * @param string|null $value
     * @return RatingSummaryInterface
     */
    public function setRatingId(?string $value): RatingSummaryInterface
    {
        return $this->setData(self::RATING_ID, $value);
    }
}
