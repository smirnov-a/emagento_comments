<?php

namespace Emagento\Comments\Model;

use Magento\Review\Model\Review as MagentoReview;

/**
 * @method mixed getUpdatedAt()
 */
class Review extends MagentoReview
{
    /**
     * Load by multiple attributes
     *
     * @param $attributes
     * @return $this
     * @throws \Exception
     */
    public function loadByAttributes($attributes)
    {
        $this->_getResource()->loadByAttributes($this, $attributes);
        return $this;
    }

    /**
     * Это для прохождения unit-теста
     * @param string|null $value
     * @return $this
     */
    public function setSource($value)
    {
        $this->setData('source', $value);
        return $this;
    }

    public function setSourceId($value)
    {
        $this->setData('source_id', $value);
        return $this;
    }

    public function setCreatedAt($value)
    {
        $this->setData('created_at', $value);
        return $this;
    }

    public function setUpdatedAt($value)
    {
        $this->setData('updated_at', $value);
        return $this;
    }

    public function setEntityPkValue($value)
    {
        $this->setData('entity_pk_value', $value);
        return $this;
    }

    public function setCustomerId($value)
    {
        $this->setData('customer_id', $value);
        return $this;
    }

    public function setStatusId($value)
    {
        $this->setData('status_id', $value);
        return $this;
    }

    public function setTitle($value)
    {
        $this->setData('title', $value);
        return $this;
    }

    public function setDetail($value)
    {
        $this->setData('detail', $value);
        return $this;
    }

    public function setNickname($value)
    {
        $this->setData('nickname', $value);
        return $this;
    }

    public function setStoreId($value)
    {
        $this->setData('store_id', $value);
        return $this;
    }

    public function setStores($value)
    {
        $this->setData('stores', $value);
        return $this;
    }
}
