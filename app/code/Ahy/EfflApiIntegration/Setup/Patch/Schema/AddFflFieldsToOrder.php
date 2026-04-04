<?php

namespace Ahy\EfflApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddFflFieldsToOrder implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $orderTable = $this->moduleDataSetup->getTable('sales_order');

        // Add "ffl_dealer" column (text)
        if (!$connection->tableColumnExists($orderTable, 'ffl_dealer')) {
            $connection->addColumn(
                $orderTable,
                'ffl_dealer',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'FFL Dealer Info'
                ]
            );
        }

        // Add "selected_ffl_dealer_id" column (int)
        if (!$connection->tableColumnExists($orderTable, 'selected_ffl_dealer_id')) {
            $connection->addColumn(
                $orderTable,
                'selected_ffl_dealer_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Selected FFL Dealer ID'
                ]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
