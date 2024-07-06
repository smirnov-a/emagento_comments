<?php

namespace Emagento\Comments\Api\Data\Rating;

interface OptionInterface
{
    public const OPTION_ID  = 'option_id';
    public const VALUE = 'value';

    /**
     * Get Option ID
     *
     * @return string|null
     */
    public function getOptionId(): ?string;

    /**
     * Set Option ID
     *
     * @param string|null $value
     * @return OptionInterface
     */
    public function setOptionId(?string $value): OptionInterface;

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
     * @return OptionInterface
     */
    public function setValue(?string $value): OptionInterface;
}
