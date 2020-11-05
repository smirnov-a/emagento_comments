<?php

namespace Local\Comments\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ReviewSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Local\Comments\Api\Data\ReviewInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Local\Comments\Api\Data\ReviewInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
