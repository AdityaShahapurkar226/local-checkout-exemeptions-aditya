<?php

namespace Ahy\EfflApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddFflFieldsToQuote implements SchemaPatchInterface
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
        $quoteTable = $this->moduleDataSetup->getTable('quote');

        // Add "ffl_dealer" column (text)
        if (!$connection->tableColumnExists($quoteTable, 'ffl_dealer')) {
            $connection->addColumn(
                $quoteTable,
                'ffl_dealer',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'FFL Dealer Info'
                ]
            );
        }

        // Add "selected_ffl_dealer_id" column (int)
        if (!$connection->tableColumnExists($quoteTable, 'selected_ffl_dealer_id')) {
            $connection->addColumn(
                $quoteTable,
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
