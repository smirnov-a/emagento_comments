<?php

namespace Emagento\Comments\Model\ResourceModel\Rating;

use Magento\Framework\DB\Select;

class Collection extends \Magento\Review\Model\ResourceModel\Rating\Collection
{
    /**
     * Magento Init Select
     *
     * @return Collection
     */
    public function _initSelect(): Collection
    {
        parent::_initSelect();
        $this->setOrder('entity_id', Select::SQL_ASC);
        return $this;
    }
}
