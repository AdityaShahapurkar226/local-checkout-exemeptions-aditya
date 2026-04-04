<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlert\Setup\Patch\Data;

use Bss\ProductStockAlert\Model\Stock;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class UpdateDataV106 implements DataPatchInterface
{
    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var Selection
     */
    protected $bundle;

    /**
     * @var Grouped
     */
    protected $grouped;

    /**
     * @var Stock
     */
    protected $modelstock;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\Stock
     */
    protected $stock;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * UpgradeData constructor.
     *
     * @param Configurable $configurable
     * @param Selection $bundle
     * @param Grouped $grouped
     * @param Stock $modelstock
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock $stock
     * @param CollectionFactory $productCollectionFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        Configurable                                     $configurable,
        Selection                                        $bundle,
        Grouped                                          $grouped,
        Stock                                            $modelstock,
        \Bss\ProductStockAlert\Model\ResourceModel\Stock $stock,
        CollectionFactory                                $productCollectionFactory,
        \Magento\Eav\Setup\EavSetupFactory               $eavSetupFactory,
        ModuleDataSetupInterface                         $setup
    )
    {
        $this->stock = $stock;
        $this->configurable = $configurable;
        $this->bundle = $bundle;
        $this->grouped = $grouped;
        $this->modelstock = $modelstock;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
    }

    /**
     * Upgrade add product_stock_alert
     *
     * @return UpdateDataV106|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'product_stock_alert',
            [
                'type' => 'int',
                'label' => 'Out of Stock Notification',
                'input' => 'select',
                'source' => \Bss\ProductStockAlert\Model\Attribute\Source\Order::class,
                'required' => false,
                'sort_order' => 57,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => true
            ]
        );

        $collection = $this->modelstock->getCollection();
        if ($collection->getSize() > 0) {
            $productIds = [];
            foreach ($collection as $alert) {
                $productIds[] = $alert->getProductId();
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $productIds]);
            $data = [];
            $parentId = null;
            foreach ($productCollection as $product) {
                $parent = $this->getParentIdConfigurable($product);
                if (!isset($parent[0])) {
                    $parent = $this->getParentIdBundle($product);
                }
                if (!isset($parent[0])) {
                    $parent = $this->getParentIdGrouped($product);
                }
                if (isset($parent[0])) {
                    $parentId = $parent[0];
                }
                $data[] = ['product_id' => $product->getId(), 'parent_id' => $parentId];
            }
            foreach ($data as $row) {
                $this->executeQueryinRow($row, $this->setup);
            }
        }
    }

    /**
     * @param array $row
     * @param ModuleDataSetupInterface $setup
     */
    private function executeQueryinRow($row, $setup)
    {
        if ($row['parent_id']) {
            $query = sprintf("UPDATE %s SET parent_id = %d WHERE product_id = %d",
                $setup->getTable('bss_product_alert_stock'),
                $row['parent_id'],
                $row['product_id']
            );
            $this->stock->executeQuery($setup, $query);
        }
    }

    /**
     * @param object $product
     * @return string[]
     */
    protected function getParentIdConfigurable($product)
    {
        return $this->configurable->getParentIdsByChild($product->getId());
    }

    /**
     * @param object $product
     * @return array
     */
    protected function getParentIdBundle($product)
    {
        return $this->bundle->getParentIdsByChild($product->getId());
    }

    /**
     * @param object $product
     * @return array
     */
    protected function getParentIdGrouped($product)
    {
        return $this->grouped->getParentIdsByChild($product->getId());
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
