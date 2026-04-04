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
namespace Bss\ProductStockAlert\Block\Product\View;

use Bss\ProductStockAlert\Model\Attribute\Source\Order;

/**
 * Recurring payment view stock
 */
class Stock extends \Bss\ProductStockAlert\Block\Product\View
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $multiSourceInventoryHelper;

    /**
     * Stock constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->multiSourceInventoryHelper = $multiSourceInventoryHelper;
        parent::__construct($context, $helper, $registry, $data);
    }

    /**
     * @param $productId
     * @param $productSku
     * @return bool|float|int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkStatusByStockId($productId, $productSku)
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getCode();
            $stockResolver = $this->multiSourceInventoryHelper->getStockResolverObject();
            $salableQty = $this->multiSourceInventoryHelper->getSalableQtyObject();
            $stockId = null;
            if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
                $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface) {
                $stockId = $stockResolver->execute('website', $websiteId)->getStockId();
            }
            $stock = $this->stockRegistry->getStockItem($productId);
            if (!$stockId) {
                return $stock->getIsInStock();
            }
            return $salableQty->execute($productSku, (int)$stockId);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!$this->helper->isStockAlertAllowed()
            || !$this->getProduct()
            || $this->getProduct()->getProductStockAlert() == Order::DISABLE &&
            $this->getProduct()->getTypeId() !== "configurable") {
            $template = '';
        } elseif (in_array($this->getProduct()->getTypeId(), ['simple', 'virtual', 'downloadable']) &&
            $this->checkStatusByStockId($this->getProduct()->getId(), $this->getProduct()->getSku())) {
            $template = '';
        } else {
            $this->setSignupUrl($this->helper->getSaveUrl('stock'));
            $this->setStatusAvailable($this->getProduct()->isAvailable());
        }
        return parent::setTemplate($template);
    }
}
