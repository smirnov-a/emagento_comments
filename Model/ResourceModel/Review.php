<?php


namespace Emagento\Comments\Model\ResourceModel;

use Magento\Review\Model\ResourceModel\Review as MagentoReview;

class Review extends MagentoReview
{
    /**
     * Load by multiple attributes
     *
     * @param \Magento\Review\Model\Review $review
     * @param array $attributes
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByAttributes(\Magento\Review\Model\Review $review, $attributes)
    {
        $where = [];
        foreach ($attributes as $attributeCode => $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getMainTable(),      // review
                ['review_id']               // pk
            )
            ->where(implode(' AND ', $where));
        $bind = $attributes;
        // взять код строки
        $reviewId = $connection->fetchOne($select, $bind);
        if ($reviewId) {
            $this->load($review, $reviewId);
        } else {
            $review->setData([]);
        }

        return $this;
    }

    /**
     * Update direct
     *
     * @param int $reviewId
     * @param array $data данные должны быть подготовлены
     * @return $this
     */
    public function updatePathAndLevel($reviewId, $data)
    {
        $this->getConnection()->update(
            $this->_reviewTable,                // table
            $data,                              // data to update
            ["review_id = ?" => $reviewId]      // condition
        );

        return $this;
    }
}
