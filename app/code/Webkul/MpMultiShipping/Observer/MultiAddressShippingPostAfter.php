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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Webkul\MpMultiShipping\Helper\Data;
use Webkul\MpMultiShipping\Logger\Logger;

class MultiAddressShippingPostAfter implements ObserverInterface
{
    public function __construct(
        SessionManager $coreSession,
        Session $checkoutSession,
        RateFactory $rateFactory,
        Data $helper,
        Logger $multiShipLogger
    ) {
        $this->coreSession = $coreSession;
        $this->checkoutSession = $checkoutSession;
        $this->rateFactory = $rateFactory;
        $this->helper = $helper;
        $this->multiShipLogger = $multiShipLogger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $requestData = $observer->getRequest()->getParams();
            $quote = $observer->getQuote();
            $multiShippingMode = $this->helper->getConfigData('shipping_mode');
            
            $multiAddressShipping = $observer->getRequest()->getParam("multi_address_cost");
            $multiAddressShippingData = $this->processMultiAddressData($multiAddressShipping, $multiShippingMode);
            
            $this->coreSession->unsMultiAddressRates();
            $this->coreSession->setMultiAddressRates($multiAddressShippingData);

            foreach ($multiAddressShippingData as $addressId => $shippingData) {
                $shipRateCollection = $this->rateFactory->create()->getCollection();
                $shipRate = $shipRateCollection->addFieldToFilter("address_id", $addressId)
                                               ->addFieldToFilter("code", "mpmultishipping_mpmultishipping")
                                               ->getLastItem();

                $shippingPrice = 0;
                if ($multiShippingMode == "1") {
                    $shippingPrice = array_sum($shippingData['sellerRates']);
                } elseif ($multiShippingMode == "2") {
                    foreach ($shippingData['sellerRates'] as $sellerShipPrice) {
                        $shippingPrice = array_sum($sellerShipPrice) + $shippingPrice;
                    }
                }
                $shipRate->setPrice($shippingPrice)->save();
            }

            $selectedShippingMethods = $this->processSelectedMethods(
                $requestData['multi_address_shipping'],
                $multiAddressShippingData,
                $multiShippingMode
            );
            $this->coreSession->unsSelectedMethods();
            $this->coreSession->setSelectedMethods($selectedShippingMethods);
        } catch (\Exception $e) {
            $this->multiShipLogger->critical("Observer Exception : ".$e->getMessage());
        }
    }

    public function processMultiAddressData($rawData, $shippingMode)
    {
        $addressShippingData = json_decode($rawData, true);
        $result = [];
        if (!empty($addressShippingData)) {
            foreach ($addressShippingData as $data) {
                $result[$data['addressId']]['customId'] = $data['customAddressId'];
                if ($shippingMode == "1") {
                    $result[$data['addressId']]['sellerRates'][$data['sellerId']] = $data['cost'];
                } elseif ($shippingMode == "2") {
                    $result[$data['addressId']]['sellerRates'][$data['sellerId']][$data['itemId']] = $data['cost'];
                }
                
            }
        }
        return $result;
    }

    public function processSelectedMethods($selectedMethods, $shippingData, $shipMode)
    {
        $shippingMethods = [];
        $sellerShippingMethods = $this->checkoutSession->getSellerMethod();
        $shippingTitle = [];
        foreach ($sellerShippingMethods as $shippingMethod) {
            if ($shipMode == 1) {
                if (isset($shippingMethod['methods'])) {
                    $sellerShippingRates = $shippingMethod['methods'];
                    foreach ($sellerShippingRates as $shippingCode => $shippingMethodData) {
                        $shippingTitle[$shippingCode] = $shippingMethodData['method'];
                    }
                }
            } else {
                foreach ($sellerShippingMethods as $method) {
                    if (isset($method['methods'])) {
                        $sellerShippingRates = $method['methods'];
                        foreach ($sellerShippingRates as $shippingCode => $shippingMethodData) {
                            $shippingTitle[$shippingCode] = $shippingMethodData['method'];
                        }
                    }
                }
            }
        }
        
        foreach ($selectedMethods as $addressId => $sellerMethods) {
            if ($shipMode == "1") {
                foreach ($sellerMethods as $sellerId => $method) {
                    $shippingMethods[$shippingData[$addressId]['customId']][] = [
                        'sellerid' => $sellerId,
                        'price' => $shippingData[$addressId]['sellerRates'][$sellerId],
                        'baseamount' => $shippingData[$addressId]['sellerRates'][$sellerId],
                        'code' => $method,
                        'method' => $shippingTitle[$method]
                    ];
                }
            } elseif ($shipMode == "2") {
                foreach ($sellerMethods as $itemId => $method) {
                    $data = [
                        'itemid' => $itemId,
                        'code' => $method,
                        'method' => $shippingTitle[$method]
                    ];
                    $sellerRates = $shippingData[$addressId]['sellerRates'];
                    foreach ($sellerRates as $sellerId => $itemPrice) {
                        if (isset($itemPrice[$itemId])) {
                            $data['sellerid'] = $sellerId;
                            $data['price'] = $itemPrice[$itemId];
                            $data['baseAmount'] = $itemPrice[$itemId];
                        }
                        
                    }
                    $shippingMethods[$shippingData[$addressId]['customId']][] = $data;
                }
            }
        }
        return $shippingMethods;
    }
}
