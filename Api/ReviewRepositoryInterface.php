<?php

namespace Emagento\Comments\Api;

use Emagento\Comments\Model\Review;
use Emagento\Comments\Model\ReviewFactory;

/**
 * Interface ReviewRepositoryInterface
 */
interface ReviewRepositoryInterface
{
    /**
     * @return ReviewFactory
     */
    public function getFactory();

    /**
     * @param Review $review
     * @return mixed
     */
    public function save(Review $review);

    /**
     * @param $reviewId
     * @return mixed
     */
    public function getById($reviewId);

    /**
     * Retrieve Review matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param Review $review
     * @return mixed
     */
    public function delete(Review $review);

    /**
     * Delete Review by ID.
     *
     * @param int $reviewId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $reviewId);
}
