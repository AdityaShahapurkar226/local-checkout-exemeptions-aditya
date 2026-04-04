<?php

declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateOrchidFflDealersTable implements SchemaPatchInterface
{
    private SchemaSetupInterface $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();

        if (!$setup->tableExists('ahy_orchid_ffl_dealers')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('ahy_orchid_ffl_dealers'))
                ->addColumn('entity_id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true], 'Primary Key')
                ->addColumn('dealer_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Dealer Name')
                ->addColumn('ffl_id', Table::TYPE_TEXT, 50, ['nullable' => false], 'FFL ID')
                ->addColumn('ffl_expiration_date', Table::TYPE_DATE, null, ['nullable' => true], 'FFL Expiration Date')
                ->addColumn('ffl_current', Table::TYPE_TINYINT, null, ['nullable' => false], 'FFL Current')
                ->addColumn('street', Table::TYPE_TEXT, 255, ['nullable' => true], 'Street')
                ->addColumn('city', Table::TYPE_TEXT, 100, ['nullable' => true], 'City')
                ->addColumn('state', Table::TYPE_TEXT, 100, ['nullable' => true], 'State')
                ->addColumn('zip_code', Table::TYPE_TEXT, 20, ['nullable' => true], 'Zip Code')
                ->addColumn('latitude', Table::TYPE_DECIMAL, '10,6', ['nullable' => true], 'Latitude')
                ->addColumn('longitude', Table::TYPE_DECIMAL, '10,6', ['nullable' => true], 'Longitude')
                ->addColumn('is_ffl_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 1], 'Is FFL Active')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'Updated At')
                ->addIndex(
                    $setup->getIdxName(
                        'ahy_orchid_ffl_dealers',
                        ['ffl_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['ffl_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Orchid FFL Dealers Table');

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
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
