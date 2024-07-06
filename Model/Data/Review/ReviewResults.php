<?php

namespace Emagento\Comments\Model\Data\Review;

use Emagento\Comments\Api\Data\Review\ReviewResultsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class ReviewResults extends AbstractSimpleObject implements ReviewResultsInterface
{
    public const KEY_ITEMS = 'items';
    public const KEY_TOTAL_COUNT = 'total_count';

    /**
     * @return \Emagento\Comments\Api\Data\Review\ReviewInterface[]
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }

    /**
     * @param \Emagento\Comments\Api\Data\Review\ReviewInterface[] $items
     * @return ReviewResults
     */
    public function setItems($items)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }

    /**
     * @param int $count
     * @return ReviewResults
     */
    public function setTotalCount($count)
    {
        return $this->setData(self::KEY_TOTAL_COUNT, $count);
    }
}
