<?php

namespace Local\Comments\Model;

use Magento\Review\Model\Rating as MagentoRating;

class Rating extends MagentoRating
{
    /**
     * Load by multiple attributes
     *
     * @param $attributes
     * @return $this
     * @throws \Exception
     */
    public function loadByAttributes($attributes) {
        $this->_getResource()->loadByAttributes($this, $attributes);
        return $this;
    }
}
