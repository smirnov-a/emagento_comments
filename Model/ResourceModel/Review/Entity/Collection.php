<?php

namespace Emagento\Comments\Model\ResourceModel\Review\Entity;

use Emagento\Comments\Model\Review\Entity;
use Emagento\Comments\Model\ResourceModel\Review\Entity as EntityResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Entity::class, EntityResource::class);
    }
}
