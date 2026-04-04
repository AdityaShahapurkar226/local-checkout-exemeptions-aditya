<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\ViewModel;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;



class HelperViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    private $orderHelper;

    /**
     * @var \Magento\Shipping\Helper\Data
     */
    private $shippingHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $catalogHelperOutput;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelperData;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $catalogHelperImage;

    /**
     * @var \Magento\Wishlist\Helper\Data $wishlistHelper
     */
    private $wishlistHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Catalog\Helper\Product\Compare $catalogHelperProductCompare
     */
    private $catalogHelperProductCompare;


    /**
     * @var SaleslistCollectionFactory
     */
    private $saleslistCollectionFactory;

    /**
     * Construct
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param \Magento\Shipping\Helper\Data $shippingHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Helper\Output $catalogHelperOutput
     * @param \Magento\Catalog\Helper\Data $catalogHelperData
     * @param \Magento\Catalog\Helper\Image $catalogHelperImage
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $catalogHelperProductCompare
     * @param \Magento\Catalog\Helper\Category $categoryhelper
     * @param \Webkul\Marketplace\Helper\Notification $notificationHelper
     * @param \Zend\Uri\Http $zendUri
     * 
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helperData,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Shipping\Helper\Data $shippingHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Output $catalogHelperOutput,
        \Magento\Catalog\Helper\Data $catalogHelperData,
        \Magento\Catalog\Helper\Image $catalogHelperImage,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $catalogHelperProductCompare,
        \Magento\Catalog\Helper\Category $categoryhelper,
        \Webkul\Marketplace\Helper\Notification $notificationHelper,
        \Zend\Uri\Http $zendUri,
        \Psr\Log\LoggerInterface $logger,
        SaleslistCollectionFactory $saleslistCollectionFactory 
    ) {
        $this->helperData = $helperData;
        $this->orderHelper = $orderHelper;
        $this->shippingHelper = $shippingHelper;
        $this->jsonHelper = $jsonHelper;
        $this->catalogHelperOutput = $catalogHelperOutput;
        $this->catalogHelperData = $catalogHelperData;
        $this->catalogHelperImage = $catalogHelperImage;
        $this->wishlistHelper = $wishlistHelper;
        $this->catalogHelperProductCompare = $catalogHelperProductCompare;
        $this->categoryhelper = $categoryhelper;
        $this->notificationHelper = $notificationHelper;
        $this->zendUri = $zendUri;
        $this->logger = $logger;
         $this->saleslistCollectionFactory = $saleslistCollectionFactory;
    }

    /**
     * Get zendUri helper
     *
     * @return \Zend\Uri\Http
     */
    public function getZendUriObj()
    {
        return $this->zendUri;
    }

    /**
     * Get Helper Data
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getHelper()
    {
        return $this->helperData;
    }

    /**
     * Get Order Helper Data
     *
     * @return \Webkul\Marketplace\Helper\Orders
     */
    public function getOrderHelper()
    {
        return $this->orderHelper;
    }

    /**
     * Get Shipping Helper Data
     *
     * @return \Magento\Shipping\Helper\Data
     */
    public function getShippingHelper()
    {
        return $this->shippingHelper;
    }

    /**
     * Get Json Helper Data
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Get Catalog Helper Output Data
     *
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogHelperOutput()
    {
        return $this->catalogHelperOutput;
    }

    /**
     * Get Catalog Helper Data
     *
     * @return \Magento\Catalog\Helper\Data
     */
    public function getCatalogHelperData()
    {
        return $this->catalogHelperData;
    }

    /**
     * Get Catalog Helper Image
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getCatalogHelperImage()
    {
        return $this->catalogHelperImage;
    }

    /**
     * Get Wishlist Helper
     *
     * @return \Magento\Wishlist\Helper\Data
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    /**
     * Get Catalog Helper Product Compare
     *
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCatalogHelperProductCompare()
    {
        return $this->catalogHelperProductCompare;
    }

    /**
     * Get category Helper
     *
     * @return \Magento\Catalog\Helper\Category $categoryhelper
     */
    public function getCategoryHelper()
    {
        return $this->categoryhelper;
    }

    /**
     * Get Notification Helper
     *
     * @return \Webkul\Marketplace\Helper\Notification $notificationHelper
     */
    public function getNotificationHelper()
    {
        return $this->notificationHelper;
    }

    /**
     * Resolve seller ID from marketplace_saleslist using productId
     */
    private function getSellerIdByProductId(int $productId): ?int
    {
        try {
            $salesList = $this->saleslistCollectionFactory->create()
                ->addFieldToFilter('mageproduct_id', $productId)
                ->getFirstItem();

            if ($salesList && $salesList->getId()) {
                return (int)$salesList->getSellerId();
            }
        } catch (\Exception $e) {
            $this->logger->error("Error fetching seller ID for product {$productId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get FFL information for the current seller in an order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getSellerFflInfo(\Magento\Sales\Model\Order $order): array
    {
        $currentSellerId = $this->helperData->getCustomerId(); // Current logged-in seller
        $this->logger->info("Current Seller ID: {$currentSellerId}");

        $sellerItems = [];
        $sellerName = 'N/A';

        foreach ($order->getAllItems() as $item) {
            $sku = $item->getSku();
            $productId = (int)$item->getProductId();

            // Try to resolve sellerId
            $itemSellerId = method_exists($item, 'getSellerId') ? $item->getSellerId() : null;
            if (!$itemSellerId && $productId) {
                $itemSellerId = $this->getSellerIdByProductId($productId);
            }

            $this->logger->info("Item SKU: {$sku} ProductId: {$productId} SellerId: " . ($itemSellerId ?: 'null'));

            $isFreeGift = ((float)$item->getPrice() <= 0) || stripos($sku, 'FREE') === 0;
            if ($isFreeGift) {
                $this->logger->info("Skipping free gift item", ['sku' => $sku]);
                continue;
            }

            if (!$itemSellerId) {
                $this->logger->warning("Order item SKU '{$sku}' has no seller_id resolved!");
                continue;
            }

            if ($itemSellerId == $currentSellerId) {
                $sellerItems[] = $item;
                $sellerName = $sellerName === 'N/A' ? "Seller #{$itemSellerId}" : $sellerName;
            }
        }

        $hasFflProduct = false;
        $hasNormalProduct = false;

        foreach ($sellerItems as $item) {
            $product = $item->getProduct();
            $fflRequired = $product ? (int)$product->getData('ffl_selection_required') : 0;
            if ($fflRequired === 1) {
                $hasFflProduct = true;
            } else {
                $hasNormalProduct = true;
            }
        }

        $dealerName = $order->getFflDealer() ?: 'N/A';

        $info = [
            'sellerName'   => $sellerName,
            'onlyFfl'      => $hasFflProduct && !$hasNormalProduct,
            'onlyNormal'   => $hasNormalProduct && !$hasFflProduct,
            'isCombo'      => $hasFflProduct && $hasNormalProduct,
            'dealerName'   => $dealerName,
            'sellerId'     => $currentSellerId
        ];

        $this->logger->info("Final Seller FFL Info", $info);

        return $info;
    }
}
