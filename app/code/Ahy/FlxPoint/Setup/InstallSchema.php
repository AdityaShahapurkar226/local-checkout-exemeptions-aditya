<?php

namespace Ahy\FlxPoint\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        // Create table flxpoint_delta
        $table = $installer->getConnection()->newTable(
            $installer->getTable('flxpoint_delta')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'last_update_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Use TYPE_TEXT for VARCHAR
            255, // Adjust the length based on your requirements
            ['nullable' => false, 'default' => ''],
            'Last update at'
        )->setComment(
            'Flxpoint Delta Table'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
?>
