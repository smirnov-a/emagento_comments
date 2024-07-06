<?php

namespace Emagento\Comments\Model\Rating\Form;

use Magento\Framework\Registry;
use Magento\Review\Model\Rating;

class DataProvider
{
    /** @var Rating|mixed|null */
    protected ?Rating $ratingModel;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->ratingModel = $registry->registry('rating_data');
    }

    /**
     * Get Rating Data
     *
     * @return array
     */
    public function getRatingData(): array
    {
        return $this->ratingModel
            ? $this->ratingModel->getData()
            : [];
    }
}
