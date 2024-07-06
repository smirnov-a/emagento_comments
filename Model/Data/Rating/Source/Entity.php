<?php

namespace Emagento\Comments\Model\Data\Rating\Source;

use Emagento\Comments\Model\ResourceModel\Review\Entity\CollectionFactory;
use Emagento\Comments\Helper\Constants;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Select;

class Entity implements OptionSourceInterface
{
    private const REVIEW_ENTITY_CODES = [
        Constants::REVIEW_ENTITY_TYPE_BY_PRODUCT,
        Constants::REVIEW_ENTITY_TYPE_BY_STORE,
    ];

    /** @var array */
    private array $options;
    /** @var CollectionFactory */
    private CollectionFactory $reviewEntityCollection;

    /**
     * @param CollectionFactory $reviewEntityCollection
     */
    public function __construct(
        CollectionFactory $reviewEntityCollection
    ) {
        $this->reviewEntityCollection = $reviewEntityCollection;
    }

    /**
     * Get Options as Array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if (isset($this->options)) {
            return $this->options;
        }

        $collection = $this->reviewEntityCollection->create();
        $collection
            ->addFieldToFilter('entity_code', ['in' => self::REVIEW_ENTITY_CODES])
            ->setOrder('entity_id', Select::SQL_ASC)
        ;
        foreach ($collection as $item) {
            $this->options[] = [
                'value' => $item->getId(),
                'label' => __(ucfirst($item->getEntityCode())),
            ];
        }

        return $this->options;
    }
}
