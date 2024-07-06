<?php

namespace Emagento\Comments\Model\ResourceModel\Review;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entity extends AbstractDb
{
    /**
     * Magento _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('review_entity', 'entity_id');
    }

    /**
     * Get Entity ID by Code
     *
     * @param string $entityCode
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityIdByCode(string $entityCode): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where($connection->prepareSqlCondition('entity_code', $entityCode));

        $id = $connection->fetchOne($select);
        return $id ? (int) $id : null;
    }
}
