<?php

namespace Emagento\Comments\Api;

interface ReviewEntityRepositoryInterface
{
    /**
     * Get Entity ID by Code
     *
     * @param string $entityCode
     * @return int|null
     */
    public function getEntityIdByCode(string $entityCode): ?int;
}
