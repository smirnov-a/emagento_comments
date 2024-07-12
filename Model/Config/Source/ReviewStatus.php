<?php

namespace Emagento\Comments\Model\Config\Source;

use Magento\Review\Helper\Data as StatusSource;

class ReviewStatus implements \Magento\Framework\Option\ArrayInterface
{
    /** @var StatusSource */
    private StatusSource $source;

    /**
     * @param StatusSource $source
     */
    public function __construct(
        StatusSource $source
    ) {
        $this->source = $source;
    }

    /**
     * Get Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->source->getReviewStatusesOptionArray();
    }

    /**
     * Get Option Array as "Key-Value" Format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
