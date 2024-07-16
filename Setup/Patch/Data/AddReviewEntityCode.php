<?php

namespace Emagento\Comments\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddReviewEntityCode implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
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

        foreach (['review_entity', 'rating_entity'] as $tableName) {
            $table = $this->moduleDataSetup->getTable($tableName);
            $select = $connection->select()
                ->from($table)
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns('entity_id')
                ->where('entity_code = ?', 'store');

            $entityId = $connection->fetchOne($select);
            if ($entityId) {
                continue;
            }

            $connection->insertForce(
                $table,
                [
                    'entity_code' => 'store'
                ]
            );
        }

        $this->logger->info('Patch applied');
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
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
