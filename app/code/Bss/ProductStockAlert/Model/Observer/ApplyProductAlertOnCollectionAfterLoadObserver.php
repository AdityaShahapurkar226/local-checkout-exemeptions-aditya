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
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Bss\ProductStockAlert\Model\Attribute\Source\Order;
use Magento\Catalog\Model\Product\Visibility;

class ApplyProductAlertOnCollectionAfterLoadObserver implements ObserverInterface
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $multiSourceInventoryHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * ApplyProductAlertOnCollectionAfterLoadObserver constructor.
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    ) {
        $this->helper = $helper;
        $this->httpContext = $httpContext;
        $this->multiSourceInventoryHelper = $multiSourceInventoryHelper;
        $this->stockState = $stockState;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($this->helper->isStockAlertAllowed()) {
            foreach ($collection as $product) {
                if ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
                    $isSalable = $this->multiSourceInventoryHelper->isEnabledMsi()
                        ? $this->checkProductSaleable($product) : $product->isAvailable();
                    if ($product->getProductStockAlert() != Order::DISABLE &&
                        !$isSalable && $this->checkCustomer()
                    ) {
                        $product->setIsStockAlertAllowed(true);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $product
     * @return bool
     */
    protected function checkProductSaleable($product)
    {
        if ($product->isAvailable()) {
            if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                $saleQty = $saleQtyNumber = $this->getSalableQty($product);
                if (is_array($saleQty) && isset($saleQty["0"]["qty"])) {
                    $saleQtyNumber = $saleQty["0"]["qty"];
                }
                if ($saleQtyNumber <= 0) {
                    return false;
                }
                return true;
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkCustomer()
    {
        $customerGroupId = $this->httpContext->getValue(
            \Bss\ProductStockAlert\Model\Customer\Context::CONTEXT_CUSTOMER_GROUP_ID
        );
        return $this->helper->checkCustomer($customerGroupId);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int|float|array
     */
    private function getSalableQty($product)
    {
        try {
            $getSalableQuantityDataBySku = $this->multiSourceInventoryHelper->getSalableQuantityDataBySkuObject();
            if ($getSalableQuantityDataBySku &&
                $getSalableQuantityDataBySku instanceof \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
            ) {
                return $getSalableQuantityDataBySku->execute($product->getSku());
            }

            $productQty = $this->stockState->getStockQty($product->getId());
            return $productQty;
        } catch (\Exception $exception) {
            return 0;
        }
    }
}
