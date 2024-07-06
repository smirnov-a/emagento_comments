<?php

namespace Emagento\Comments\Model\ResourceModel;

use Magento\Review\Model\ResourceModel\Review as MagentoReview;

class Review extends MagentoReview
{
    /**
     * Update Review Path and Level
     *
     * @param int $reviewId
     * @param array $data
     * @return Review
     */
    public function updatePathAndLevel(int $reviewId, array $data): static
    {
        $this->getConnection()->update(
            $this->_reviewTable,
            $data,
            ['review_id = ?' => $reviewId]
        );

        return $this;
    }
}
