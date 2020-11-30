<?php


namespace Emagento\Comments\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Emagento\Comments\Api\Data\ReviewInterface;

interface ReviewRepositoryInterface
{
    /**
     * Save review.
     *
     * @param \Emagento\Comments\Api\Data\ReviewInterface $review
     * @return \Emagento\Comments\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(ReviewInterface $review);

    /**
     * @param int $reviewId
     * @return \Emagento\Comments\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($reviewId);

    /**
     * Retrieve review matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Emagento\Comments\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param ReviewInterface $review
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(ReviewInterface $review);

    /**
     * Delete review by ID.
     *
     * @param int $reviewId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($reviewId);
}
