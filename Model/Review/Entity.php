<?php

namespace Emagento\Comments\Model\Review;

class Entity extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Emagento\Comments\Model\ResourceModel\Review\Entity::class);
    }
}
