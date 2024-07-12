<?php

namespace Emagento\Comments\Model\Data\Review;

use Emagento\Comments\Api\Data\Review\ReviewResultsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class ReviewResults extends AbstractSimpleObject implements ReviewResultsInterface
{
    public const KEY_ITEMS = 'items';
    public const KEY_TOTAL_COUNT = 'total_count';

    /**
     * Get Items
     *
     * @return \Emagento\Comments\Api\Data\Review\ReviewInterface[]
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }

    /**
     * Set Items
     *
     * @param \Emagento\Comments\Api\Data\Review\ReviewInterface[] $items
     * @return ReviewResults
     */
    public function setItems($items)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * Get Total Count
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }

    /**
     * Set Total Count
     *
     * @param int $count
     * @return ReviewResults
     */
    public function setTotalCount($count)
    {
        return $this->setData(self::KEY_TOTAL_COUNT, $count);
    }
}
