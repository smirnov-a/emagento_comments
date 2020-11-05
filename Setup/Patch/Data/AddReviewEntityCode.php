<?php
/**
 * добавляет тип комментария "К магазину"
 */
namespace Local\Comments\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddReviewEntityCode implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
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
        // добавить тип комментария к магазину ('store')
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('review_entity'),
            [
                'entity_id' => \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                'entity_code' => 'store'
            ]
        );
        // то же самое rating/rating_entity
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('rating_entity'),
            [
                'entity_id' => \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
                'entity_code' => 'store'
            ]
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
