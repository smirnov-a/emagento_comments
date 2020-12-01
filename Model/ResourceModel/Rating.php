<?php

namespace Emagento\Comments\Model\ResourceModel;

use Magento\Review\Model\ResourceModel\Rating as MagentoRating;

class Rating extends MagentoRating
{
    /**
     * Load by multiple attributes
     *
     * @param \Magento\Review\Model\Rating $rating
     * @param array $attributes
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByAttributes(\Magento\Review\Model\Rating $rating, $attributes)
    {
        $where = [];
        foreach ($attributes as $attributeCode => $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getMainTable(),      // rating
                ['rating_id']               // pk
            )
            ->where(implode(' AND ', $where));
        $bind = $attributes;
        // взять код строки
        $ratingId = $connection->fetchOne($select, $bind);
        if ($ratingId) {
            $this->load($rating, $ratingId);
        } else {
            $rating->setData([]);
        }

        return $this;
    }
}
