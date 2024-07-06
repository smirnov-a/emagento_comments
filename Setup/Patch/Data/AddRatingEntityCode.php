<?php

namespace Emagento\Comments\Setup\Patch\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Psr\Log\LoggerInterface;
use Emagento\Comments\Helper\Data as Helper;

class AddRatingEntityCode implements DataPatchInterface, PatchVersionInterface
{
    /** @var ModuleDataSetupInterface */
    private ModuleDataSetupInterface $moduleDataSetup;
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var Helper  */
    private Helper $helper;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     * @param Helper|null $helper
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger,
        Helper $helper = null,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->helper = $helper ?: ObjectManager::getInstance()->get(Helper::class);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $storeEntityId = $this->helper->getStoreReviewEntityId();
        $connection = $this->moduleDataSetup->getConnection();
        $tableRating = $this->moduleDataSetup->getTable('rating');
        $select = $connection->select()
            ->from($tableRating)
            ->reset(Select::COLUMNS)
            ->columns('rating_id')
            ->where('entity_id = ?', $storeEntityId);

        $ratingId = $connection->fetchOne($select);
        if (!$ratingId) {
            $connection->insert(
                $tableRating,
                [
                    'entity_id'   => $storeEntityId,
                    'rating_code' => 'Store',
                    'position'    => 0,
                ]
            );
            $ratingId = $connection->lastInsertId($tableRating);
        }

        $tableRatingOption = $this->moduleDataSetup->getTable('rating_option');
        $select = $connection->select()
            ->from($tableRatingOption)
            ->reset(Select::COLUMNS)
            ->columns('COUNT(*)')
            ->where('rating_id = ?', $ratingId);

        if (!$connection->fetchOne($select)) {
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
                $tableRatingOption,
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
