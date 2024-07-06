<?php

namespace Emagento\Comments\Model\ResourceModel\Review;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Store extends AbstractDb
{
    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('review_store', 'review_id');
        $this->_isPkAutoIncrement = false;
    }

    /**
     * Get Reviews Data
     *
     * @param array $reviewIds
     * @param int|null $storeId
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReviewsStoreData(array $reviewIds, ?int $storeId = null): ?array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where($connection->prepareSqlCondition('review_id', ['in' => $reviewIds]));
        if ($storeId) {
            $select->where($connection->prepareSqlCondition('store_id', $storeId));
        }

        return $connection->fetchAll($select);
    }
}
