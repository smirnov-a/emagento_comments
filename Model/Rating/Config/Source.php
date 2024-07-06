<?php

namespace Emagento\Comments\Model\Rating\Config;

use Magento\Review\Model\ResourceModel\Rating\CollectionFactory;
use Emagento\Comments\Helper\Constants;

class Source implements \Magento\Framework\Option\ArrayInterface
{

    /** @var CollectionFactory */
    protected $ratingFactory;

    /**
     * @param CollectionFactory $ratingFactory
     */
    public function __construct(CollectionFactory $ratingFactory)
    {
        $this->ratingFactory = $ratingFactory;
    }

    /**
     * Get Option Array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];

        $ratingCollection = $this->ratingFactory->create();
        $ratingCollection->addEntityFilter(Constants::REVIEW_ENTITY_TYPE_BY_STORE);
        foreach ($ratingCollection as $rating) {
            $result[] = [
                'value' => $rating->getRatingId(),
                'label' => $rating->getRatingCode(),
            ];
        }

        return $result;
    }

}
