<?php

namespace Ahy\EstateApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddAgeFieldsToSalesOrder implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $setup;

    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public function apply()
    {
        $this->setup->startSetup();
        $connection = $this->setup->getConnection();

        // age_verified
        if (!$connection->tableColumnExists('sales_order', 'age_verified')) {
            $connection->addColumn(
                'sales_order',
                'age_verified',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 1,
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Age Verified',
                ]
            );
        }

        // age_of_purchaser
        if (!$connection->tableColumnExists('sales_order', 'age_of_purchaser')) {
            $connection->addColumn(
                'sales_order',
                'age_of_purchaser',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment'  => 'Age Of Purchaser',
                ]
            );
        }

        $this->setup->endSetup();
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
