<?php

namespace Emagento\Comments\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ReviewSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Emagento\Comments\Api\Data\ReviewInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Emagento\Comments\Api\Data\ReviewInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
