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
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Helper;

use Magento\Framework\App\Helper\Context;

class MultiSourceInventory extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Const
     */
    const MSI_MODULE_CORE = 'Magento_Inventory';

    /**
     * @var null
     */
    private $getSalableQuantityDataBySku;

    /**
     * @var null
     */
    private $stockResolver;

    /**
     * @var null
     */
    private $salableQty;

    /**
     * @var null
     */
    private $isProductSalable;

    /**
     * @var null
     */
    private $areProductsSalable;

    /**
     * @var MultiSourceInventoryFactory
     */
    private $multiSourceInventoryFactory;

    /**
     * MultiSourceInventory constructor.
     *
     * @param Context $context
     * @param MultiSourceInventoryFactory $multiSourceInventoryFactory
     * @param null $getSalableQuantityDataBySku
     * @param null $stockResolver
     * @param null $salableQty
     * @param null $areProductsSalable
     */
    public function __construct(
        Context $context,
        MultiSourceInventoryFactory $multiSourceInventoryFactory,
        $getSalableQuantityDataBySku = null,
        $stockResolver = null,
        $salableQty = null,
        $isProductSalable = null,
        $areProductsSalable = null
    ) {
        $this->multiSourceInventoryFactory = $multiSourceInventoryFactory;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->stockResolver = $stockResolver;
        $this->salableQty = $salableQty;
        $this->isProductSalable = $isProductSalable;
        $this->areProductsSalable = $areProductsSalable;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabledMsi()
    {
        return $this->_moduleManager->isEnabled(self::MSI_MODULE_CORE);
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku|null
     */
    public function getSalableQuantityDataBySkuObject()
    {
        if ($this->isEnabledMsi()) {
            return $this->multiSourceInventoryFactory->create($this->getSalableQuantityDataBySku);
        }
        return null;
    }

    /**
     * @return \Magento\InventorySalesApi\Api\StockResolverInterface|null
     */
    public function getStockResolverObject()
    {
        if ($this->isEnabledMsi()) {
            return $this->multiSourceInventoryFactory->create($this->stockResolver);
        }
        return null;
    }

    /**
     * @return \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null
     */
    public function getSalableQtyObject()
    {
        if ($this->isEnabledMsi()) {
            return $this->multiSourceInventoryFactory->create($this->salableQty);
        }
        return null;
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku|\Magento\InventorySalesApi\Api\AreProductsSalableInterface|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|\Magento\InventorySalesApi\Api\StockResolverInterface|null
     */
    public function getAreProductsSalableObject()
    {
        if ($this->isEnabledMsi()) {
            return $this->multiSourceInventoryFactory->create($this->areProductsSalable);
        }
        return null;
    }

    /**
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku|\Magento\InventorySalesApi\Api\IsProductSalableInterface|\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|\Magento\InventorySalesApi\Api\StockResolverInterface|null
     */
    public function getIsProductSalableObject()
    {
        if ($this->isEnabledMsi()) {
            return $this->multiSourceInventoryFactory->create($this->isProductSalable);
        }
        return null;
    }
}
