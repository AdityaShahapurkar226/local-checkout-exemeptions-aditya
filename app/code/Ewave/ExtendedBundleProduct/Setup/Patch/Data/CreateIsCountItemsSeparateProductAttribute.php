<?php

namespace Ewave\ExtendedBundleProduct\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Ewave\ExtendedBundleProduct\Helper\Data;
use Ewave\ExtendedBundleProduct\Model\Config\Source\Bundle\CountSeparatelyOption;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class CreateIsCountItemsSeparateProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeCode = 'is_separate_cart_items';

        $attributeId = $eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'attribute_id');

        if ($attributeId) {
            return null;
        }

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            Data::CODE_ATTRIBUTE_BUNDLE_IS_COUNT_ITEMS_SEPARATE,
            [
                'group' => 'Bundle Items',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Count Bundle Items Separately',
                'input' => 'select',
                'class' => '',
                'source' => CountSeparatelyOption::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => CountSeparatelyOption::VALUE_USE_CONFIG,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'bundle'
            ]
        );
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
