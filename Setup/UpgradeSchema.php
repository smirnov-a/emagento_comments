<?php

namespace Emagento\Comments\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $tableName = $setup->getTable('review');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'source' => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 32,
                        'nullable' => false,
                        'default'  => 'local',
                        'comment'  => 'Source type (local/yandex/flamp)',
                    ],
                    'source_id' => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 32,
                        'nullable' => false,
                        'default'  => '',
                        'comment'  => 'Source comment id',
                    ],
                    'updated_at' => [
                        'type'     =>Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment'  => 'Source date edit',
                    ],
                    'parent_id' => [
                        'type'     => Table::TYPE_BIGINT,
                        'nullable' => true,
                        'comment'  => 'Parent comment id',
                    ],
                    'level' => [
                        'type'     => Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'defualt'  => 0,
                        'comment'  => 'Comment\'s level',
                    ],
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }

        $setup->endSetup();
    }
}
