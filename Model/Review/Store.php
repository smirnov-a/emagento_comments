<?php

namespace Emagento\Comments\Model\Review;

use Magento\Framework\Model\AbstractModel;

class Store extends AbstractModel
{
    /**
     * Magento Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Emagento\Comments\Model\ResourceModel\Review\Store::class);
    }
}
