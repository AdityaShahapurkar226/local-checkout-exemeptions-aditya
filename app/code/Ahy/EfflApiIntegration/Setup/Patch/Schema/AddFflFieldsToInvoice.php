<?php

declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddFflFieldsToInvoice implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->getConnection()->startSetup();
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('sales_invoice');

        // Add "ffl_dealer" column (text)
        if (!$connection->tableColumnExists($tableName, 'ffl_dealer')) {
            $connection->addColumn(
                $tableName,
                'ffl_dealer',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'FFL Dealer Info',
                ]
            );
        }

        // Add "selected_ffl_dealer_id" column (int)
        if (!$connection->tableColumnExists($tableName, 'selected_ffl_dealer_id')) {
            $connection->addColumn(
                $tableName,
                'selected_ffl_dealer_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Selected FFL Dealer ID',
                ]
            );
        }

        $setup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
