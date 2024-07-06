<?php

namespace Emagento\Comments\Model\ResourceModel\Review;

use Emagento\Comments\Model\Review;
use Emagento\Comments\Model\ResourceModel\Review as ReviewResource;
use Magento\Framework\DB\Select;
use Magento\Review\Model\ResourceModel\Review\Collection as MagentoCollection;
use Emagento\Comments\Helper\Constants;

class Collection extends MagentoCollection
{
    private const IS_RAND = false;
    private const LEVEL_ONE = 1;
    private const LEVEL_TWO = 2;

    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Review::class, ReviewResource::class);
    }

    /**
     * Magento _initSelect
     *
     * @return $this
     */
    public function _initSelect(): static
    {
        parent::_initSelect();
        return $this->addStoreReviewFilter();
    }

    /**
     * Get Main Table Column array
     *
     * @return array
     */
    public function getMainTableColumns(): array
    {
        return [
            Review::REVIEW_ID, Review::CREATED_AT, Review::STATUS_ID, Review::SOURCE,
            Review::SOURCE_ID, Review::PARENT_ID, Review::LEVEL, Review::PATH, Review::ENTITY_ID,
        ];
    }

    /**
     * Add Store Review Filter
     *
     * @return $this
     */
    private function addStoreReviewFilter(): static
    {
        $connection = $this->getConnection();
        $this->getSelect()
            ->join(
                ['review_entity' => $this->getTable('review_entity')],
                'review_entity.entity_id = main_table.entity_id',
                []
            )
            ->where(
                $connection->prepareSqlCondition('review_entity.entity_code', Constants::REVIEW_ENTITY_TYPE_BY_STORE)
            )
        ;
        return $this;
    }

    /**
     * Add First Level Review Reply
     *
     * @param int $page
     * @param int $limit
     * @param bool $isRand
     * @return $this
     */
    public function addReviewReplyOneLevel(
        int $page = Constants::PAGE,
        int $limit = Constants::LIMIT,
        bool $isRand = self::IS_RAND
    ): static {
        $offset = $limit * ($page - 1);
        $this->addFieldToFilter('main_table.status_id', Review::STATUS_APPROVED)
            ->addFieldToFilter('main_table.level', self::LEVEL_ONE)
            ->getSelect()
                ->joinLeft(
                    ['main_table2' => $this->getTable('review')],
                    'main_table.review_id = main_table2.parent_id AND main_table2.level = ' . self::LEVEL_TWO,
                    [
                        'r_review_id'  => 'review_id',
                        'r_level'      => 'level',
                        'r_created_at' => 'created_at',
                    ]
                )
                ->joinLeft(
                    ['detail2' => $this->getTable('review_detail')],
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
            $this->setOrder('review_id', Select::SQL_DESC);
        }

        return $this;
    }

    /**
     * Add Rating Data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addRatingData(): static
    {
        $connection = $this->getConnection();
        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$connection->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr("COUNT(*)");
        $mainTableColumns = $this->getMainTableColumns();

        $select = $this->getSelect()
            ->joinLeft(
                ['rating_vote' => $this->getTable('rating_option_vote')],
                'rating_vote.review_id = main_table.review_id',
                ['sum' => $sumColumn, 'count' => $countColumn]
            )
            ->joinLeft(
                ['review_store' => $this->getTable('review_store')],
                'rating_vote.review_id=review_store.review_id',
                ['review_store.store_id']
            )
            ->group(
                array_merge(
                    $mainTableColumns,
                    [
                        'detail.detail_id', 'detail.store_id', 'detail.title', 'detail.detail',
                        'detail.nickname', 'detail.customer_id', 'review_store.store_id'
                    ]
                )
            )
        ;
        if (!$this->_storeManager->isSingleStoreMode()) {
            $storeId = $this->_storeManager->getStore()->getId();
            $select->join(
                ['rating_store' => $this->getTable('rating_store')],
                'rating_store.rating_id = rating_vote.rating_id AND rating_store.store_id = review_store.store_id',
                []
            )->where(
                $connection->prepareSqlCondition('review_store.store_id', $storeId)
            );
        }

        return $this;
    }
}
