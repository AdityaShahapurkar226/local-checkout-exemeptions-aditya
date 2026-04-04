<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Setup\Patch\Data;

use Ahy\Ffl\Setup\FflSetup;
use Ahy\Ffl\Setup\FflSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DefaultFflEntity implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var FflSetup
     */
    private $fflSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FflSetupFactory $fflSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FflSetupFactory $fflSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fflSetupFactory = $fflSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var FflSetup $customerSetup */
        $fflSetup = $this->fflSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $fflSetup->installEntities();
        

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
        
        ];
    }
}

