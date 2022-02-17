<?php

namespace Emagento\Comments\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddRatingEntityCode implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('rating');
        $select = $connection->select()
            ->from($table)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('rating_id')
            ->where('entity_id = ?', \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE);

        $ratingId = $connection->fetchOne($select);
        if (!$ratingId) {
            $connection->insert(
                $table,
                [
                    'entity_id'   => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                    'rating_code' => 'Store',
                    'position'    => 0,
                ]
            );
            //Fill table rating/rating_option
            $ratingId = $connection->lastInsertId($table);
        }
        $table = $this->moduleDataSetup->getTable('rating_option');
        $select = $connection->select()
            ->from($table)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('COUNT(*)')
            ->where('rating_id = ?', $ratingId);

        $cnt = $connection->fetchOne($select);
        if (!$cnt) {
            $optionData = [];
            for ($i = 1; $i <= 5; $i++) {
                $optionData[] = [
                    'rating_id' => $ratingId,
                    'code'      => (string) $i,
                    'value'     => $i,
                    'position'  => $i
                ];
            }
            $connection->insertMultiple(
                $table,
                $optionData
            );
        }

        $this->logger->info('Patch applied');
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            \Emagento\Comments\Setup\Patch\Data\AddReviewEntityCode::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
