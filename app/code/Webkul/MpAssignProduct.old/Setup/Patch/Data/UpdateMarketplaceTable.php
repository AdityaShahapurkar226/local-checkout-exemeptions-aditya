<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Setup\Patch\Data;

/**
 * MpAssignProduct UpdateMarketplaceTable
 *
 * @author      Webkul Software Private Limited
 */
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class UpdateMarketplaceTable implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    protected $controllersRepository;

    /**
     * ChangePriceAttributeDefaultScope constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * insert MpAssignProduct controller's data
         */
        $marketplaceControllerListTable = $this->moduleDataSetup->getTable('marketplace_controller_list');
        $data = [];

        if (!count($this->controllersRepository->getByPath('mpassignproduct/product/view'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAssignProduct',
                'controller_path' => 'mpassignproduct/product/view',
                'label' => 'Assign Product',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpassignproduct/product/export'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAssignProduct',
                'controller_path' => 'mpassignproduct/product/productlist',
                'label' => 'Assign Product List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $this->moduleDataSetup->getConnection()
            ->insertMultiple($marketplaceControllerListTable, $data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
