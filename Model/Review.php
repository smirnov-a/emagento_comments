<?php

namespace Local\Comments\Model;

use Magento\Review\Model\Review as MagentoReview;

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
}
