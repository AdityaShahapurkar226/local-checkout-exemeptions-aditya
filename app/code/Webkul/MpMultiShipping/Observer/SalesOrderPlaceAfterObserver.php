<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersFactory;
use Webkul\MpMultiShipping\Logger\Logger;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var string
     */
    private $methodForOrder = null;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Webkul\MpMultiShipping\Logger\Logger
     */
    private $logger;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Webkul\Marketplace\Model\OrdersFactory
     */
    private $mpOrdersFactory;

    /**
     * @param SessionManager $session
     * @param CheckoutSession $checkoutSession
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param MpOrdersFactory $mpOrdersFactory
     * @param Logger $logger
     */
    public function __construct(
        SessionManager $session,
        CheckoutSession $checkoutSession,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        MpOrdersFactory $mpOrdersFactory,
        Logger $logger
    ) {
        $this->saleslistFactory = $saleslistFactory;
        $this->session = $session;
        $this->itemFactory = $itemFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_currencyFactory = $currencyFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->_storeManager = $store;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->logger = $logger;
    }

    /**
     * after place order event handler
     * Distribute Shipping Price for sellers
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $orders = $order ? [$order] : $observer->getOrders();
            foreach ($orders as $order) {
                $quote = $this->quoteFactory->create()->load($order->getQuoteId());
                $shippingmethod = $order->getShippingMethod();
                $lastOrderId = $order->getId();
                $fromCurrency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
                $toCurrency = $this->_storeManager->getStore()->getDefaultCurrency()->getCode();
                if (strpos($shippingmethod, 'mpmultishipping') !== false) {
                    $allorderitems = $order->getAllItems();
                    $shipmethod = explode('_', $shippingmethod, 2);
                    $shippingAll = $this->session->getSelectedMethods() ? $this->session->getSelectedMethods() : [];
                    if ($quote->isMultipleShippingAddresses()) {
                        $shippingAll = $shippingAll[$order->getShippingAddress()->getCustomerAddressId()];
                    }
                    $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $fieldId = "carriers/mpmultishipping/shipping_mode";
                    $enabledProductWise = $this->scopeConfig->getValue($fieldId, $scopeStore);

                    if ($enabledProductWise == 1) {
                        $this->filterCollection($fromCurrency, $toCurrency, $shippingAll, $lastOrderId);
                    }
                    /**
                     * for saving shipping rates item wise
                     * 2 for product wise shiping enabled from admin config
                     */
                    if ($enabledProductWise == 2) {
                        $this->updateProductWise($fromCurrency, $toCurrency, $shippingAll, $lastOrderId, $order);
                    }
                    $order->setShippingDescription("Multishipping -".$this->methodForOrder);
                    $this->saveObj($order);
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('SalesOrderPlaceAfterObserver : '.$e->getMessage());
        }
        $this->session->unsShippingMethod();
        $this->session->unsMultiAddressRates();
        $this->checkoutSession->setSellerMethod('');
    }

    /**
     * filter Collection Product Wise
     *
     * @param [string] $fromCurrency
     * @param [string] $toCurrency
     * @param [object] $shippingAll
     * @param [int] $lastOrderId
     * @param [object] $order
     * @return void
     */
    public function updateProductWise($fromCurrency, $toCurrency, $shippingAll, $lastOrderId, $order)
    {
        $totalSellerShippingArr = [];
        foreach ($shippingAll as $shippingMethodsSelected) {
            if (!isset($shippingMethodsSelected['sellerid'])) {
                foreach ($shippingMethodsSelected as $data) {
                    $price = $data['price'];
                    $sellerId = $data['sellerid'];
                    $shipMethod = $data['method'];
                    $shippingMethod = $data['code'];
                    $quoteItemId = $data['itemid'];

                    $totalSellerShippingArr = $this->populateTotalSellerShippingArr(
                        $totalSellerShippingArr,
                        $data,
                        $sellerId
                    );

                    $price = $this->getConvertedPrice($fromCurrency, $toCurrency, $price);
                    $orderItemId = $this->getorderItemIdFromQuoteItemId($quoteItemId);
                    $this->updateMpSalesList($orderItemId, $shippingMethod, $price);
                    $this->updateMpOrders($lastOrderId, $sellerId, $shipMethod);
                }
            } else {
                $price = $shippingMethodsSelected['price'];
                $sellerId = $shippingMethodsSelected['sellerid'];
                $shipMethod = $shippingMethodsSelected['method'];
                $shippingMethod = $shippingMethodsSelected['code'];
                $quoteItemId = $shippingMethodsSelected['itemid'];
                
                $totalSellerShippingArr = $this->populateTotalSellerShippingArr(
                    $totalSellerShippingArr,
                    $shippingMethodsSelected,
                    $sellerId
                );

                $price = $this->getConvertedPrice($fromCurrency, $toCurrency, $price);
                $orderItemId = $this->getorderItemIdFromQuoteItemId($quoteItemId);
                $this->updateMpSalesList($orderItemId, $shippingMethod, $price);
                $this->updateMpOrders($lastOrderId, $sellerId, $shipMethod);

            }
        }

        $totalShippingAmount = 0;
        foreach ($totalSellerShippingArr as $sellerId => $shippingAmount) {
            $totalShippingAmount += $shippingAmount;
            $collection = $this->mpOrdersFactory->create()->getCollection()
                        ->addFieldToFilter('order_id', ['eq'=>$lastOrderId])
                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                        ->setPageSize(1)->getFirstItem();

            $collection->setShippingCharges($shippingAmount);
            $this->saveObj($collection);
        }
        
        $order->setShippingAmount($totalShippingAmount);
    }

    /**
     * update MpSalesList model
     *
     * @param [int] $orderItemId
     * @param [string] $shippingMethod
     * @param [int] $price
     * @return void
     */
    public function updateMpSalesList($orderItemId, $shippingMethod, $price)
    {
        $mpSalesList = $this->saleslistFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_item_id', $orderItemId)
                ->getFirstItem();
        $mpSalesList->setShippingMethod($shippingMethod)
        ->setShippingPrice($price);
        $this->saveObj($mpSalesList);
    }

    /**
     * update MpOrders model
     *
     * @param [int] $lastOrderId
     * @param [int] $sellerId
     * @param [string] $shipMethod
     * @return void
     */
    public function updateMpOrders($lastOrderId, $sellerId, $shipMethod)
    {
        $mpOrderscollection = $this->mpOrdersFactory->create()->getCollection()
                    ->addFieldToFilter('order_id', ['eq'=>$lastOrderId])
                    ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                    ->setPageSize(1)->getFirstItem();

        $carrierName = $mpOrderscollection->getCarrierName();

        if ($carrierName != null) {
            $carrierName = $carrierName.', '.$shipMethod;
            $mpOrderscollection->setCarrierName($carrierName);
        } else {
            $mpOrderscollection->setCarrierName($shipMethod);
        }
        $this->saveObj($mpOrderscollection);
    }

    /**
     * converts price to different curency
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int $price
     * @return int $price
     **/
    public function convertPriceToCurrency($fromCurrencyCode, $toCurrencyCode, $price)
    {
        $rate = $this->_currencyFactory->create()->load($fromCurrencyCode)->getAnyRate($toCurrencyCode);
        $price = $price * $rate;
        return $price;
    }

    /**
     * get order_item_id from quote_item_id
     *
     * @param int $quoteItemId
     * @return int
     */
    public function getorderItemIdFromQuoteItemId($quoteItemId)
    {
        $data = $this->itemFactory->create()->getCollection()
                                            ->addFieldToFilter('quote_item_id', $quoteItemId)
                                            ->getFirstItem();
        return $data->getItemId();
    }

    /**
     * returns price as per store config
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int $price
     * @return int
     */
    public function getConvertedPrice($fromCurrency, $toCurrency, $price)
    {
        if ($fromCurrency != $toCurrency) {
            $price = $this->convertPriceToCurrency($fromCurrency, $toCurrency, $price);
        }
        return $price;
    }

    /**
     * Filter Collection
     *
     * @param [string] $fromCurrency
     * @param [string] $toCurrency
     * @param [array] $shippingAll
     * @param [int] $lastOrderId
     * @return void
     */
    public function filterCollection($fromCurrency, $toCurrency, $shippingAll, $lastOrderId)
    {
        foreach ($shippingAll as $shipdata) {
            if (!isset($shipdata['price'])) {
                foreach ($shipdata as $data) {
                    $price = $data['price'];
                    $sellerId = $data['sellerid'];
                    $shipMethod = $data['method'];
                    $shipCode = $data['code'];
                    $this->setFilter(
                        $fromCurrency,
                        $toCurrency,
                        $price,
                        $lastOrderId,
                        $sellerId,
                        $shipMethod,
                        $shipCode
                    );
                }
            } else {
                $price = $shipdata['price'];
                $sellerId = $shipdata['sellerid'];
                $shipMethod = $shipdata['method'];
                $shipCode = $shipdata['code'];
                $this->setFilter($fromCurrency, $toCurrency, $price, $lastOrderId, $sellerId, $shipMethod, $shipCode);
            }
        }
    }

    /**
     * Set Filter
     *
     * @param [string] $fromCurrency
     * @param [string] $toCurrency
     * @param [int] $price
     * @param [int] $lastOrderId
     * @param [int] $sellerId
     * @param [string] $shipMethod
     * @param [string] $shipCode
     * @return void
     */
    public function setFilter($fromCurrency, $toCurrency, $price, $lastOrderId, $sellerId, $shipMethod, $shipCode)
    {
        $price = $this->getConvertedPrice($fromCurrency, $toCurrency, $price);
        $collection = $this->mpOrdersFactory->create()->getCollection()
                        ->addFieldToFilter('order_id', ['eq'=>$lastOrderId])
                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                        ->setPageSize(1)->getFirstItem();
        $this->checkAndUpdateCollection($collection, $shipMethod, $shipCode, $price);
    }

    /**
     * checks and updates collection
     *
     * @param [collection] $collection
     * @param [string] $shipMethod
     * @param [string] $shipCode
     * @param [int] $price
     * @return void
     */
    public function checkAndUpdateCollection($collection, $shipMethod, $shipCode, $price)
    {
        try {
            if ($collection->getId()) {
                if (empty($this->methodForOrder)) {
                    $this->methodForOrder = $shipMethod;
                } else {
                    $this->methodForOrder .= ", ".$shipMethod;
                }
                $collection->setCarrierName($shipMethod);
                $collection->setShippingCharges($price);
                $collection->setMultishipMethod($shipCode);
                $collection->save();
            }
        } catch (\Exception $e) {
            $this->logger->addError('SalesOrderPlaceAfterObserver : '.$e->getMessage());
        }
    }

    /**
     * calculates seller wise shipping
     *
     * @param array $totalSellerShippingArr
     * @param array $shippingMethodsSelected
     * @param int $sellerId
     * @return array
     */
    public function populateTotalSellerShippingArr($totalSellerShippingArr, $shippingMethodsSelected, $sellerId)
    {
        try {
            if (isset($totalSellerShippingArr[$sellerId])) {
                $totalSellerShippingArr[$sellerId] += $shippingMethodsSelected['price'];
            } else {
                $totalSellerShippingArr[$sellerId] = $shippingMethodsSelected['price'];
            }
            return $totalSellerShippingArr;
        } catch (\Exception $e) {
            return $totalSellerShippingArr;
        }
    }

    /**
     * calls save at object
     *
     * @param object $object
     * @return void
     */
    public function saveObj($object)
    {
        $object->save();
    }
}
