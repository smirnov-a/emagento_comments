<?php

namespace Emagento\Comments\Model\Data\Rating;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Rating\RatingInterface;

class Rating extends AbstractSimpleObject implements RatingInterface
{
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
     * @return RatingInterface
     */
    public function setRatingId(?string $value): RatingInterface
    {
        return $this->setData(self::RATING_ID, $value);
    }

    /**
     * Get Entity ID
     *
     * @return string|null
     */
    public function getEntityId(): ?string
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set Entity ID
     *
     * @param string|null $value
     * @return RatingInterface
     */
    public function setEntityId(?string $value): RatingInterface
    {
        return $this->setData(self::ENTITY_ID, $value);
    }

    /**
     * Get Rating Code
     *
     * @return string|null
     */
    public function getRatingCode(): ?string
    {
        return $this->_get(self::RATING_CODE);
    }

    /**
     * Set Rating Code
     *
     * @param string|null $value
     * @return RatingInterface
     */
    public function setRatingCode(?string $value): RatingInterface
    {
        return $this->setData(self::RATING_CODE, $value);
    }

    /**
     * Get Options
     *
     * @return \Emagento\Comments\Api\Data\Rating\OptionInterface[]|null
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * Set Options
     *
     * @param \Emagento\Comments\Api\Data\Rating\OptionInterface[]|null $value
     * @return RatingInterface
     */
    public function setOptions($value): RatingInterface
    {
        return $this->setData(self::OPTIONS, $value);
    }
}
