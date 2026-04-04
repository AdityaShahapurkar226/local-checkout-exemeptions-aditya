<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ControllersDataSave implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        /**
         * insert massupload controller's data
         */
        $data = [];

        if (!count($this->controllersRepository->getByPath('mpmassupload/product/view'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/product/view',
                'label' => 'Mass Upload Product',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpmassupload/product/export'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/product/export',
                'label' => 'MassUpload Product Export',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mpmassupload/dataflow/profile'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/dataflow/profile',
                'label' => 'Mass Upload Dataflow Profile',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (count($data)) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
            $this->moduleDataSetup->getConnection()->endSetup();
        }
    }

    /**
     * Alias
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
