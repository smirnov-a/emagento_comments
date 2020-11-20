<?php
/**
 * Добавляет рейтинг для магазина в таблицу 'rating'
 */
namespace Local\Comments\Setup\Patch\Data;

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
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('rating'),
            [
                'entity_id' => \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                'rating_code' => 'Store',
                'position' => 0,
            ]
        );
        //Fill table rating/rating_option
        $ratingId = $this->moduleDataSetup->getConnection()->lastInsertId(
            $this->moduleDataSetup->getTable('rating')
        );
        $optionData = [];
        for ($i = 1; $i <= 5; $i++) {
            $optionData[] = ['rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
        }
        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('rating_option'),
            $optionData
        );

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
