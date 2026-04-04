<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webkul\MpSellerBuyerCommunication\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Customer\Model\Customer;
use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     * @param Customer $customer
     * @param SellerBuyerCommunication $sellerBuyerCommunication
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository,
        Customer $customer,
        SellerBuyerCommunication $sellerBuyerCommunication
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
        $this->customer = $customer;
        $this->sellerBuyerCommunication = $sellerBuyerCommunication;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        if (!count($this->controllersRepository->getByPath('mpsellerbuyercommunication/seller/history'))) {
            $data[] = [
                'module_name' => 'Webkul_MpSellerBuyerCommunication',
                'controller_path' => 'mpsellerbuyercommunication/seller/history',
                'label' => 'My Communication History',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);

        $tableName = $this->moduleDataSetup->getTable('marketplace_sellerbuyercommunication');

        if ($this->moduleDataSetup->getConnection()->isTableExists($tableName) == true) {
            $collection = $this->sellerBuyerCommunication->getCollection();
            foreach ($collection as $data) {
                $customer_name = $this->customer->load($data->getCustomerId())->getName();
                if (!empty($customer_name)) {
                    $this->sellerBuyerCommunication->load($data->getEntityId());
                    $this->sellerBuyerCommunication->setCustomerName($customer_name);
                    $this->sellerBuyerCommunication->save();
                }
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

   /**
    * Get Aliases
    *
    * @return void
    */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependecies
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
