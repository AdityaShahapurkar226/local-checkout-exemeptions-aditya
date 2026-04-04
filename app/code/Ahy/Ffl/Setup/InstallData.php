<?php
namespace Ahy\Ffl\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    private $quoteSetupFactory;
    private $salesSetupFactory;

    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $quoteInstaller = $this->quoteSetupFactory->create(['setup' => $setup]);
        $salesInstaller = $this->salesSetupFactory->create(['setup' => $setup]);

        $quoteInstaller->addAttribute(
            'quote',
            'selected_ffl_centre_id',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true]
        );

        $quoteInstaller->addAttribute(
            'quote',
            'selected_ffl_centre_address',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order',
            'selected_ffl_centre_id',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order',
            'selected_ffl_centre_address',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'nullable' => true]
        );
    }
}
