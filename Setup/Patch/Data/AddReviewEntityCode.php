<?php
/**
 * добавляет тип комментария "К магазину"
 */
namespace Emagento\Comments\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddReviewEntityCode implements DataPatchInterface, PatchVersionInterface
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
        // добавить тип комментария к магазину ('store') в review_entity и rating_entity
        foreach (['review_entity', 'rating_entity'] as $tableName) {
            $table = $this->moduleDataSetup->getTable($tableName);
            $select = $connection->select()
                ->from($table)
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns('entity_id')
                ->where('entity_code=?', 'store');
            //echo $select; exit;
            $entityId = $connection->fetchOne($select); //var_dump($entityId); exit;
            if (!$entityId) {
                $connection->insertForce(
                    $table,
                    [
                        'entity_id' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                        'entity_code' => 'store'
                    ]
                );
            }
        }
        /*
        // то же самое rating/rating_entity
        $table = $this->moduleDataSetup->getTable('rating_entity');
        $select = $connection->select()
            ->from($table)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('entity_id')
            ->where('entity_code=?', 'store');
        $connection->insertForce(
            $table,
            [
                'entity_id' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                'entity_code' => 'store'
            ]
        );
        */
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
        // патч выполнится, если версия модуля из module.xml не больше этого значения
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
