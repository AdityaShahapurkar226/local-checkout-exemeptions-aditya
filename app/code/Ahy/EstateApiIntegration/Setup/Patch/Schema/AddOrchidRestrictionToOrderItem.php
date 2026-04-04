<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddOrchidRestrictionToOrderItem implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        if (!$connection->tableColumnExists(
            $this->moduleDataSetup->getTable('sales_order_item'),
            'orchid_restriction_level'
        )) {
            $connection->addColumn(
                $this->moduleDataSetup->getTable('sales_order_item'),
                'orchid_restriction_level',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Orchid Restriction Level (1,2,3,5 or alphanumeric)'
                ]
            );
        }

        return $this;
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
