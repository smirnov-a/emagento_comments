<?php

namespace Emagento\Comments\Model\Review;

use Emagento\Comments\Api\ReviewEntityRepositoryInterface;
use Emagento\Comments\Model\ResourceModel\Review\Entity as ReviewEntityResource;

class EntityRepository implements ReviewEntityRepositoryInterface
{
    /** @var ReviewEntityResource */
    private $resource;

    /**
     * @param ReviewEntityResource $resource
     */
    public function __construct(
        ReviewEntityResource $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get Entity ID by Code
     *
     * @param string $entityCode
     * @return int|null
     */
    public function getEntityIdByCode(string $entityCode): ?int
    {
        return $this->resource->getEntityIdByCode($entityCode);
    }
}
