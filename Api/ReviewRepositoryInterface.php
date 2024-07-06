<?php

namespace Emagento\Comments\Api;

use Emagento\Comments\Model\ReviewFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ReviewRepositoryInterface
{
    /**
     * Save Review
     *
     * @param ReviewInterface $review
     * @return ReviewInterface
     */
    public function save(ReviewInterface $review);

    /**
     * Get Review by ID
     *
     * @param int $reviewId
     * @return ReviewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $reviewId);

    /**
     * Get List of Review
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get List of Review by Attributes
     *
     * @param array $attributes
     * @return ReviewInterface|null
     */
    public function getByAttributes(array $attributes);

    /**
     * Delete
     *
     * @param ReviewInterface $review
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(ReviewInterface $review);

    /**
     * Delete by ID
     *
     * @param int $reviewId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById(int $reviewId): bool;

    /**
     * Update Path and Level of Review
     *
     * @param int $reviewId
     * @param array $data
     * @return void
     */
    public function updatePathAndLevel(int $reviewId, array $data): void;

    /**
     * Get Review Factory
     *
     * @return ReviewFactory
     */
    public function getFactory();
}
