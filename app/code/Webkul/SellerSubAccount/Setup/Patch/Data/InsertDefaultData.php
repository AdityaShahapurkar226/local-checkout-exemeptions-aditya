<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

class InsertDefaultData implements DataPatchInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

   /**
    * @param ControllersRepository $controllersRepository
    * @param GroupFactory $groupFactory
    * @param CollectionFactory $groupCollectionFactory
    * @param ModuleDataSetupInterface $moduleDataSetup
    */
    public function __construct(
        ControllersRepository $controllersRepository,
        GroupFactory $groupFactory,
        CollectionFactory $groupCollectionFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $data = [];

        if (!count($this->controllersRepository->getByPath('sellersubaccount/account/'))) {
            $data[] = [
                'module_name' => 'Webkul_SellerSubAccount',
                'controller_path' => 'sellersubaccount/account/',
                'label' => 'Manage Accounts',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);

            $groupCollection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('customer_group_code', 'Sub Account');
        if (!$groupCollection->getSize()) {
            // Create the new group
            /** @var \Magento\Customer\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setCode('Sub Account')
                  ->setTaxClassId(3)
                  ->save();
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
