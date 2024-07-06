<?php

namespace Emagento\Comments\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\ResourceModel\Rating as MagentoRating;

class Rating extends MagentoRating
{
    private const ACTIVE = 1;

    /**
     * Get Rating ID by Code
     *
     * @param string $entityCode
     * @return int|null
     * @throws LocalizedException
     */
    public function getRatingIdByCode(string $entityCode): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                $this->getIdFieldName()
            )
            ->join(
                ['rating_entity' => $this->getTable('rating_entity')],
                'rating_entity.entity_id = main_table.entity_id',
                []
            )
            ->where($connection->prepareSqlCondition('entity_code', $entityCode))
            ->order('position')
            ->limit(1)
        ;

        $id = $connection->fetchOne($select);
        return $id ? (int) $id : null;
    }

    /**
     * Get RatingOptions by Code
     *
     * @param string $entityCode
     * @return array
     * @throws LocalizedException
     */
    public function getRatingOptionsByCode(string $entityCode): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                ['rating_id', 'rating_code']
            )
            ->join(
                ['rating_entity' => $this->getTable('rating_entity')],
                'rating_entity.entity_id = main_table.entity_id',
                []
            )
            ->join(
                ['rating_option' => $this->getTable('rating_option')],
                'rating_option.rating_id = main_table.rating_id',
                ['option_id', 'value']
            )
            ->where($connection->prepareSqlCondition('rating_entity.entity_code', $entityCode))
            ->where($connection->prepareSqlCondition('main_table.is_active', self::ACTIVE))
        ;
        return $connection->fetchAll($select);
    }
}
