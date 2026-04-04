<?php declare(strict_types=1);
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpFixedRateshipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpFixedRateshipping\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Attributes implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    protected $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var ControllersRepository
     */
    protected $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * This is apply Method
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        if (!($this->controllersRepository->getByPath('mpfixrate/shipping/view')->getSize())) {
            $data[] = [
                'module_name' => 'Webkul_MpFixedRateshipping',
                'controller_path' => 'mpfixrate/shipping/view',
                'label' => 'Fix Rate Shipping',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'mpshipping_fixrate',
            [
                'type' => 'varchar',
                'label' => 'Vendor Fixed Shipping Rate',
                'input' => 'text',
                'frontend_class' => 'validate-number',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 1000,
                'position' => 1000,
                'system' => 0,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mpshipping_fixrate')
        ->addData(
            [
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
            ]
        );

        $attribute->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'mpshipping_fixrate_upto',
            [
                'type' => 'varchar',
                'label' => 'Vendor Free Shipping From Amount',
                'input' => 'text',
                'frontend_class' => 'validate-number',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 1001,
                'position' => 1001,
                'system' => 0,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mpshipping_fixrate_upto')
        ->addData(
            [
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
            ]
        );

        $attribute->save();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
