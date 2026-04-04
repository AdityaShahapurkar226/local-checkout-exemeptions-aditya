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
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Order status
     */
    const ORDER_YES = 1;
    const ORDER_OUT_OF_STOCK = 2;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var array
     */
    protected $map_r = [];

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $multiSourceInventoryHelper;

    /**
     * ProductData constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Customer $customer
     * @param Data $helper
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customer,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->configurableData = $configurableData;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->helper = $helper;
        $this->multiSourceInventoryHelper = $multiSourceInventoryHelper;
        parent::__construct($context);
    }

    /**
     * Get All Data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllData($subject)
    {
        $result = [];
        $product = $subject->getProduct();
        if ($this->helper->isStockAlertAllowed()) {
            $productId = $product->getId();
            $parentUrl = $product->getProductUrl();
            $result['entity'] = $productId;
            $stockId = null;

            $storeId = $this->storeManager->getStore()->getId();
            $websiteId = $this->storeManager->getWebsite()->getCode();

            try {
                $stockResolver = $this->multiSourceInventoryHelper->getStockResolverObject();
                $salableQty = $this->multiSourceInventoryHelper->getSalableQtyObject();
                if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
                    $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
                ) {
                    $stockId = $stockResolver->execute('website', $websiteId)->getStockId();
                }
            } catch (\Exception $exception) {
                $stockId = null;
            }

            foreach ($subject->getAllowProducts() as $child) {
                $childProduct = [];
                $childProduct['entity'] = $child->getId();
                $preOrder = $child->getResource()->getAttributeRawValue($child->getId(), 'preorder', $storeId);
                $childStock = $this->stockRegistry->getStockItem($childProduct['entity']);
                $stockNumber = $this->getStockNumber($child, $websiteId, $childStock);
                $childProduct['stock_number'] = $stockNumber;
                $childProduct['stock_status'] = $this->isChildInStock(
                    $child->getSku(),
                    $childStock,
                    $stockId,
                    $salableQty
                );
                $childProduct['parent_url'] = $parentUrl;
                $childProduct['preorder'] = (
                    $preOrder == self::ORDER_YES ||
                    ($preOrder == self::ORDER_OUT_OF_STOCK && !$childStock->getIsInStock())
                );
                $result['child'][$child->getId()] = $childProduct;
            }
        }
        return $result;
    }

    /**
     * @param string $sku
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $childStock
     * @param int|null $stockId
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @return bool
     */
    private function isChildInStock(
        $sku,
        $childStock,
        $stockId,
        $salableQty
    ) {
        try {
            if (!$stockId) {
                return $childStock->getIsInStock();
            }
            return $salableQty->execute($sku, (int)$stockId);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * If All Child Out Of Stock, don't show option for Configurable Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isShowOptionConfigurableProduct($product)
    {
        $check = false;
        if ($this->helper->isStockAlertAllowed()) {
            $parentProduct = $this->configurableData->getChildrenIds($product->getId());
            foreach ($parentProduct[0] as $simpleProduct) {
                $childProduct['entity'] = $simpleProduct;
                $childStock = $this->stockRegistry->getStockItem($childProduct['entity']);
                if ($childStock->getIsInStock()) {
                    $check = true;
                    continue;
                }
            }
        }

        return $check;
    }

    /**
     * Get website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites()
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @param \Magento\Catalog\Model\Product $child
     * @param $websiteId
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $childStock
     * @return float
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStockNumber($child, $websiteId, $childStock)
    {
        try {
            $stockResolver = $this->multiSourceInventoryHelper->getStockResolverObject();
            $salableQty = $this->multiSourceInventoryHelper->getSalableQtyObject();

            if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
                $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface) {
                $stockId = $stockResolver->execute('website', $websiteId)->getStockId();
                return $salableQty->execute($child->getSku(), (int)$stockId);
            }
            return $childStock->getQty();
        } catch (\Exception $exception) {
            return 0;
        }
    }
}
