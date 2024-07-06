<?php

namespace Emagento\Comments\Model\Data\Rating;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Rating\OptionInterface;

class Option extends AbstractSimpleObject implements OptionInterface
{
    /**
     * Get Option ID
     *
     * @return string|null
     */
    public function getOptionId(): ?string
    {
        return $this->_get(self::OPTION_ID);
    }

    /**
     * Set Option ID
     *
     * @param string|null $value
     * @return OptionInterface
     */
    public function setOptionId(?string $value): OptionInterface
    {
        return $this->setData(self::OPTION_ID, $value);
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
     * @return OptionInterface
     */
    public function setValue(?string $value): OptionInterface
    {
        return $this->setData(self::VALUE, $value);
    }
}
