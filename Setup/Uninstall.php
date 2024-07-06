<?php

namespace Emagento\Comments\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use \Emagento\Comments\Helper\Data as Helper;

class Uninstall implements UninstallInterface
{
    /** @var Helper  */
    private Helper $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $defaultConnection = $setup->getConnection();

        $reviewTable = $setup->getTable('review');
        foreach (['source', 'source_id', 'updated_at', 'parent_id', 'level', 'path'] as $column) {
            $defaultConnection->dropColumn($reviewTable, $column);
        }

        $storeEntityId = $this->helper->getStoreReviewEntityId();
        $defaultConnection->delete(
            $setup->getTable('review_entity'),
            ['entity_id = ?', $storeEntityId]
        );

        $defaultConnection->delete(
            $setup->getTable('core_config_data'),
            "path LIKE 'local_comments/%'"
        );

        $setup->endSetup();
    }
}
