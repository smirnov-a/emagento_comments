<?php

namespace Local\Comments\Model\Source\Rating;

use Magento\Framework\Data\OptionSourceInterface;

class Entity implements OptionSourceInterface   //implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Options for select
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => 1,
                    'label' => 'Product'
                ],
                [
                    'value' => \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                    'label' => 'Store'
                ],
            ];
        }
        return $this->options;
    }
}
