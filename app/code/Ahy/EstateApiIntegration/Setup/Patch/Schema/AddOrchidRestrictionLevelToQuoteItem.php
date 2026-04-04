<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class AddOrchidRestrictionLevelToQuoteItem implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName  = $this->moduleDataSetup->getTable('quote_item');

        if ($connection->isTableExists($tableName)
            && !$connection->tableColumnExists($tableName, 'orchid_restriction_level')
        ) {
            $connection->addColumn(
                $tableName,
                'orchid_restriction_level',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => true,
                    'comment' => 'Orchid Restriction Level'
                ]
            );
        }

        $this->moduleDataSetup->endSetup();
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
