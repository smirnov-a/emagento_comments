<?php

namespace Emagento\Comments\Model\ResourceModel\Review;

use Magento\Review\Model\ResourceModel\Review\Collection as MagentoCollection;

class Collection extends MagentoCollection
{
    /**
     * @return $this
     */
    public function addStoreReviewFilter()
    {
        $this->addFieldToFilter(
            'main_table.entity_id',
            ['eq' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE]
        );

        return $this;
    }

    /**
     * Add replies on reviews. One per review
     *
     * @param int $page
     * @param int $limit
     * @param bool $isRand default by id DESC
     * @return Collection
     */
    public function addReviewReplyOneLevel($page = 1, $limit = 5, $isRand = false)
    {
        $offset = $limit * ($page - 1);
        // self join table, use fields 'r_XXX'
        $this->addStoreReviewFilter()
            ->addFieldToFilter(
                'main_table.status_id',
                \Magento\Review\Model\Review::STATUS_APPROVED
            )
            // reviews on 1st level
            ->addFieldToFilter('main_table.level', 1)
            ->getSelect()
            // replies on second level
            ->joinLeft(
                ['main_table2' => 'review'],
                'main_table.review_id = main_table2.parent_id AND main_table2.level = 2',
                [
                    'r_review_id' => 'review_id',
                    'r_level'     => 'level',
                ]
            )
            ->joinLeft(
                ['detail2' => 'review_detail'],
                'main_table2.review_id = detail2.review_id',
                [
                    'r_detail_id'   => 'detail_id',
                    'r_title'       => 'title',
                    'r_detail'      => 'detail',
                    'r_nickname'    => 'nickname',
                    'r_customer_id' => 'customer_id',
                ]
            )
            ->limit($limit, $offset);

        if ($isRand) {
            $this->getSelect()->orderRand('review_id');
        } else {
            $this->setOrder('review_id', 'DESC');
        }

        return $this;
    }
}
