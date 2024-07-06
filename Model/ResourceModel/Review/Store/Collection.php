<?php

namespace Emagento\Comments\Model\ResourceModel\Review\Store;

use Emagento\Comments\Model\Review\Store;
use Emagento\Comments\Model\ResourceModel\Review\Store as SoreResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @var string[] */
    protected $_idFieldName = ['review_id', 'store_id'];

    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Store::class, SoreResource::class);
    }
}
