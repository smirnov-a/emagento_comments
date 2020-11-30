<?php

namespace Emagento\Comments\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class ReviewStoreFilter implements CustomFilterInterface
{
    /**
     * Apply custom store filter to collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        //var_dump($filter); exit;
        /** @var \Emagento\Comments\Model\ResourceModel\Review\Collection $collection */
        $collection->addStoreFilter($filter->getValue(), false);

        return true;
    }
}
