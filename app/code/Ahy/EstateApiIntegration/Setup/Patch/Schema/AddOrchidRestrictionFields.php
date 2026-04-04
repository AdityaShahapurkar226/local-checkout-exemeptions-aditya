<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddOrchidRestrictionFields implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $columnDefinition = [
            'type'    => Table::TYPE_TEXT,
            'length'  => 32,
            'nullable'=> true,
            'default' => null,
            'comment' => 'Orchid Restriction Level (1,2,3,5 or alphanumeric)'
        ];

        // Quote table
        if (!$connection->tableColumnExists(
            $this->moduleDataSetup->getTable('quote'),
            'orchid_restriction_level'
        )) {
            $connection->addColumn(
                $this->moduleDataSetup->getTable('quote'),
                'orchid_restriction_level',
                $columnDefinition
            );
        }

        // Sales Order table
        if (!$connection->tableColumnExists(
            $this->moduleDataSetup->getTable('sales_order'),
            'orchid_restriction_level'
        )) {
            $connection->addColumn(
                $this->moduleDataSetup->getTable('sales_order'),
                'orchid_restriction_level',
                $columnDefinition
            );
        }

        return $this;
    }

    public static function getDependencies() : array
    {
        return [];
    }

    public function getAliases() : array
    {
        return [];
    }
}
