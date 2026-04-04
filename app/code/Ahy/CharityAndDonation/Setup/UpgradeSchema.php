<?php
namespace Ahy\CharityAndDonation\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get 'sales_order' and 'quote' tables
        $salesOrder = $installer->getTable('sales_order');
        $quote = $installer->getTable('quote');

        // Define attributes
        $attributes = [
            'donation_amount' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'comment' => 'Donation Amount',
            ],
            'hasDonationAppliedFlag' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => true,
                'default' => '0',
                'comment' => 'Has Donation Applied Flag',
            ],
        ];

        $connection = $installer->getConnection();

        // Check if we are upgrading from version 1.0.0 or later
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            // Add columns to 'sales_order'
            foreach ($attributes as $name => $definition) {
                $connection->addColumn($salesOrder, $name, $definition);
            }

            // Add columns to 'quote'
            foreach ($attributes as $name => $definition) {
                $connection->addColumn($quote, $name, $definition);
            }
        }

        $installer->endSetup();
    }
}
