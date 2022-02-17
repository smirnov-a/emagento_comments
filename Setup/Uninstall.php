<?php

namespace Emagento\Comments\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Invoked when remove-data flag is set during module uninstall.
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $defaultConnection = $setup->getConnection();

        $reviewTable = $setup->getTable('review');
        foreach (['source', 'source_id', 'updated_at', 'parent_id', 'level', 'path'] as $col) {
            $defaultConnection->dropColumn($reviewTable, $col);
        }

        $defaultConnection->delete(
            $setup->getTable('review_entity'),
            ['entity_id = ?', \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE]
        );

        $defaultConnection->delete(
            $setup->getTable('core_config_data'),
            "path LIKE 'local_comments/%'"
        );

        $setup->endSetup();
    }
}
