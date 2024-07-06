<?php

namespace Emagento\Comments\Api;

interface RatingRepositoryInterface
{
    /**
     * Get Rating ID by Code
     *
     * @param string $entityCode
     * @return int|null
     */
    public function getRatingIdByCode(string $entityCode): ?int;

    /**
     * Get Rating Options by Code
     *
     * @param string $entityCode
     * @return array
     */
    public function getRatingOptionsByCode(string $entityCode): array;
}
