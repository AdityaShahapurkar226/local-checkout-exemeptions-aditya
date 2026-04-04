<?php
/**
 * Webkul Software Pvt. Ltd.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceBaseShipping\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Config;

class CreateAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * Length attribute
     */
    const ATTRIBUTE_CODE_LENGTH = 'ts_dimensions_length';

    /**
     * Wifth attribute
     */
    const ATTRIBUTE_CODE_WIDTH = 'ts_dimensions_width';

    /**
     * Height attribute
     */
    const ATTRIBUTE_CODE_HEIGHT = 'ts_dimensions_height';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$this->isProductAttributeExists(self::ATTRIBUTE_CODE_LENGTH)) {
            $this->createLengthAttribute($eavSetup);
        }

        if (!$this->isProductAttributeExists(self::ATTRIBUTE_CODE_WIDTH)) {
            $this->createWidthAttribute($eavSetup);
        }

        if (!$this->isProductAttributeExists(self::ATTRIBUTE_CODE_HEIGHT)) {
            $this->createHeightAttribute($eavSetup);
        }
    }

    /**
     * is product attribute exists
     * @param string $field $field
     * @return boolean
     */
    private function isProductAttributeExists($field)
    {
        $attr = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
        return ($attr && $attr->getId()) ? true : false;
    }

    /**
     * create Length Attribute
     * @param object $eavSetup
     * @return void
     */
    private function createLengthAttribute($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::ATTRIBUTE_CODE_LENGTH,
            [
                'type' => 'decimal',
                'label' => 'Item Length',
                'input' => 'text',
                'required' => false,
                'class' => 'not-negative-amount',
                'sort_order' => 65,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'user_defined' => false,
                'apply_to' => Type::TYPE_SIMPLE
            ]
        );
    }

    /**
     * create Width Attribute
     * @param object $eavSetup
     * @return void
     */
    private function createWidthAttribute($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::ATTRIBUTE_CODE_WIDTH,
            [
                'type' => 'decimal',
                'label' => 'Item Width',
                'input' => 'text',
                'required' => false,
                'class' => 'not-negative-amount',
                'sort_order' => 66,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'user_defined' => false,
                'apply_to' => Type::TYPE_SIMPLE
            ]
        );
    }

    /**
     * create Height Attribute
     * @param object $eavSetup
     * @return void
     */
    private function createHeightAttribute($eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::ATTRIBUTE_CODE_HEIGHT,
            [
                'type' => 'decimal',
                'label' => 'Item Height',
                'input' => 'text',
                'required' => false,
                'class' => 'not-negative-amount',
                'sort_order' => 67,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'user_defined' => false,
                'apply_to' => Type::TYPE_SIMPLE
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
