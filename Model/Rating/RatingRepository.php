<?php

namespace Emagento\Comments\Model\Rating;

use Emagento\Comments\Api\RatingRepositoryInterface;
use Emagento\Comments\Model\ResourceModel\Rating as RatingResource;

class RatingRepository implements RatingRepositoryInterface
{
    /** @var RatingResource */
    private RatingResource $resource;

    /**
     * @param RatingResource $resource
     */
    public function __construct(
        RatingResource $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get Rating ID by Code
     *
     * @param string $entityCode
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRatingIdByCode(string $entityCode): ?int
    {
        return $this->resource->getRatingIdByCode($entityCode);
    }

    /**
     * Get Rating Options by Code
     *
     * @param string $entityCode
     * @return array
     */
    public function getRatingOptionsByCode(string $entityCode): array
    {
        return $this->resource->getRatingOptionsByCode($entityCode);
    }
}
