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
namespace Webkul\MpMultiShipping\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Magento\Framework\UrlInterface as UrlInterface;
use Webkul\MpMultiShipping\Logger\Logger as MpMultiShipLog;

/**
 * Marketplace Multi shipping.
 *
 */
class Carrier extends \Webkul\MarketplaceBaseShipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    const CODE = 'mpmultishipping';

    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data.
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data.
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;
    
    /**]
     * @var \Webkul\MpMultiShipping\Helper\Data
     */
    protected $_currentHelper;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $_regionFactory;

    /**
     * Raw rate request data.
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;

    protected $_defaultCarriers = [
        'mpups',
        'marketplaceusps',
        'mpdhl',
        'mpfedex',
        'mppercountry',
        'webkulshipping',
        'mpfreeshipping',
        'mpfixrate',
        'mpauspost',
        'mpeasypost',
        'mpcorreios',
        'mpfrenet',
        'mpshipstation'
    ];

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param SessionManager $coreSession
     * @param \Webkul\Marketplace\Model\Orders $mpSalesModel
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpMultiShipping\Helper\Data $currentHelper
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param PriceCurrencyInterface $priceCurrency
     * @param OptionFactory $optionFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param ShippingSettingRepository $shippingSettingRepository
     * @param MpMultiShipLog $mpMultiShipLog
     * @param UrlInterface $urlInterface $name
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession,
        \Webkul\Marketplace\Model\Orders $mpSalesModel,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpMultiShipping\Helper\Data $currentHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\App\RequestInterface $requestInterface,
        PriceCurrencyInterface $priceCurrency,
        OptionFactory $optionFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        ShippingSettingRepository $shippingSettingRepository,
        UrlInterface $urlInterface,
        MpMultiShipLog $mpMultiShipLog,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->mpSalesModel = $mpSalesModel;
        $this->_coreSession = $coreSession;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_currentHelper = $currentHelper;
        $this->_regionFactory = $regionFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->jsonSerializer = $jsonSerializer;
        $this->requestInterface = $requestInterface;
        $this->priceCurrency = $priceCurrency;
        $this->optionFactory = $optionFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->urlInterface = $urlInterface;
        $this->mpMultiShipLog = $mpMultiShipLog;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $rateResultFactory,
            $rateMethodFactory,
            $regionFactory,
            $coreSession,
            $checkoutSession,
            $customerSession,
            $currencyFactory,
            $storeManager,
            $localeFormat,
            $jsonHelper,
            $requestInterface,
            $priceCurrency,
            $optionFactory,
            $customerFactory,
            $addressFactory,
            $mpProductFactory,
            $productFactory,
            $saleslistFactory,
            $shippingSettingRepository,
            $data
        );
    }

    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        try {
            $routeName = $this->requestInterface->getRouteName();
            if (!$this->getConfigFlag('active')) {
                return false;
            }
            $this->setRequest($request);
            $activeCarriresList = $this->_currentHelper->getActiveCarriers();
            $carriersObjectArray = $this->_getObjects($activeCarriresList);
            $sellerShipping=[];
            
            $enabledProductWiseMode = $this->getMultiShippingMode();
            foreach ($carriersObjectArray as $value) {
                
                $shippingRates = $value['value']->getShippingPricedetail($this->_rawRequest, $request);
                $shippingRates = (array)$shippingRates;

                if (isset($shippingRates['shippinginfo'])) {
                    foreach ($shippingRates['shippinginfo'] as $shipdata) {

                        $sellerName = $this->customerFactory->create()->load($shipdata['seller_id'])->getName();
                        /**
                         * will be executed when product wise shipping is disabled
                         * @return shipping rates seller wise
                         */
                        if (!$enabledProductWiseMode) {
                            if (isset($sellerShipping[$shipdata['seller_id']])) {
                                if (isset($sellerShipping[$shipdata['seller_id']][$shipdata['methodcode']])) {
                                    $submethod = $sellerShipping[$shipdata['seller_id']][$shipdata['methodcode']];
                                    foreach ($shipdata['submethod'] as $submethoddetail) {
                                        array_push($submethod, $submethoddetail);
                                    }
                                    $sellerShipping[$shipdata['seller_id']][$shipdata['methodcode']] = $submethod;
                                } else {
                                    $sellerShipping[$shipdata['seller_id']][$shipdata['methodcode']] =
                                        $shipdata['submethod'];
                                    $sellerShipping[$shipdata['seller_id']]['products'] = $shipdata['product_name'];
                                    $sellerShipping[$shipdata['seller_id']]['item_ids'] = $shipdata['item_ids'];
                                }
                            } else {
                                $sellerShipping[$shipdata['seller_id']][$shipdata['methodcode']] =
                                    $shipdata['submethod'];
                                $sellerShipping[$shipdata['seller_id']]['products'] = $shipdata['product_name'];
                                $sellerShipping[$shipdata['seller_id']]['item_ids'] = $shipdata['item_ids'];
                                $sellerShipping[$shipdata['seller_id']]['seller_name'] = $sellerName;
                            }
                        } else {
                            /**
                             * will be executed when product wise shipping is enabled
                             * @return shipping rates item wise
                             */
                            if (isset($sellerShipping[$shipdata['item_ids']])) {
                                if (isset($sellerShipping[$shipdata['item_ids']][$shipdata['methodcode']])) {
                                    $submethod = $sellerShipping[$shipdata['item_ids']][$shipdata['methodcode']];
                                    foreach ($shipdata['submethod'] as $submethoddetail) {
                                        array_push($submethod, $submethoddetail);
                                    }
                                    $sellerShipping[$shipdata['item_ids']][$shipdata['methodcode']] = $submethod;
                                } else {
                                    $sellerShipping[$shipdata['item_ids']][$shipdata['methodcode']] =
                                        $shipdata['submethod'];
                                    $sellerShipping[$shipdata['item_ids']]['products'] = $shipdata['product_name'];
                                    $sellerShipping[$shipdata['item_ids']]['item_ids'] = $shipdata['item_ids'];
                                    $sellerShipping[$shipdata['item_ids']]['seller_id'] = $shipdata['seller_id'];
                                }
                            } else {
                                if (count(explode(",", $shipdata['item_ids'])) > 1) {
                                    $counter = count(explode(",", $shipdata['item_ids']));
                                    $itemIds = explode(",", $shipdata['item_ids']);
                                    $productName = explode(",", $shipdata['product_name']);
                                    $i = 0;

                                    foreach ($itemIds as $key) {
                                        foreach ($shipdata['submethod'] as $attri => $data) {
                                            foreach ($data as $k => $value) {
                                                if ($k == 'cost' && isset($shipdata['item_price_details'][$key])) {
                                                    $shipdata['submethod'][$attri]['cost'] =
                                                        $shipdata['item_price_details'][$key];
                                                    $shipdata['submethod'][$attri]['base_amount'] =
                                                        $shipdata['item_price_details'][$key];
                                                    $check = true;
                                                    break;
                                                }
                                            }
                                        }
                                        $sellerShipping[$key][$shipdata['methodcode']] = $shipdata['submethod'];
                                        $sellerShipping[$key]['products'] = $productName[$i];
                                        $sellerShipping[$key]['item_ids'] = $key;
                                        $sellerShipping[$key]['seller_name'] = $sellerName;
                                        $sellerShipping[$key]['seller_id'] = $shipdata['seller_id'];
                                        $i++;
                                    }
                                } else {
                                    $sellerShipping[$shipdata['item_ids']][$shipdata['methodcode']] =
                                        $shipdata['submethod'];
                                    $sellerShipping[$shipdata['item_ids']]['products'] = $shipdata['product_name'];
                                    $sellerShipping[$shipdata['item_ids']]['item_ids'] = $shipdata['item_ids'];
                                    $sellerShipping[$shipdata['item_ids']]['seller_name'] = $sellerName;
                                    $sellerShipping[$shipdata['item_ids']]['seller_id'] = $shipdata['seller_id'];
                                }
                            }
                        }
                    }
                }
            }
            $rates = $this->_storeManager->getStore()->getBaseCurrency()->getRate(
                $this->_storeManager->getStore()->getCurrentCurrency()
            );
            $sellerMethods = [];
            $r = $this->_rawRequest;
            $sellerProduct = $r->getSellerProductInfo();
            
            foreach ($sellerShipping as $key => $value) {

                $shippingdata = [];
                $sellerdata = null;
                if (!$enabledProductWiseMode) {
                    $shippingdata['seller_id'] = $key;
                } else {
                    $shippingdata['seller_id'] = $value['seller_id'];
                }
                $shippingdata['item_ids'] = $value['item_ids'];

                $shippingdata['products'] = implode(',', $sellerProduct[$key]);
                $shippingdata['seller_name'] = $value['seller_name'];
                if (!$enabledProductWiseMode) {
                    if ($key == 0) {
                        $shippingdata['seller_name'] = __('Admin');
                    } else {
                        $sellerdata = $this->customerFactory->create()->load($key)->getAllowedShipping();
                        $sellerdata = $sellerdata ? $this->jsonSerializer->unserialize($sellerdata) : [];
                    }
                } else {
                    $sellerId = $shippingdata['seller_id'];
                    if ($sellerId == 0) {
                        $shippingdata['seller_name'] = __('Admin');
                    } else {
                        $sellerdata = $this->customerFactory->create()->load($sellerId)->getAllowedShipping();
                        $sellerdata = $sellerdata ? $this->jsonSerializer->unserialize($sellerdata) : [];
                    }
                }
                
                $code = '';
                $methodArray= [];
                foreach ($value as $method => $details) {
                    if (is_array($details)) {
                        if (!$enabledProductWiseMode) {
                            if ($sellerdata != null && in_array($method, $sellerdata)) {
                                foreach ($details as $mcode => $mvalue) {
                                    $value = $this->_localeFormat->getNumber($mvalue['cost']);
                                    $value = $this->priceCurrency->round($rates * $value);
                                    $details[$mcode]['cost'] = $value;
                                    $methodArray[$method.'_'.$mcode] = $details[$mcode];
                                }
                            } elseif ($key == 0) {
                                foreach ($details as $mcode => $mvalue) {
                                    $value = $this->_localeFormat->getNumber($mvalue['cost']);
                                    $value = $this->priceCurrency->round($rates * $value);
                                    $details[$mcode]['cost'] = $value;
                                    $methodArray[$method.'_'.$mcode] = $details[$mcode];
                                }
                            }
                        } else {
                            foreach ($details as $mcode => $mvalue) {
                                $value = $this->_localeFormat->getNumber($mvalue['cost']);
                                $value = $this->priceCurrency->round($rates * $value);
                                $details[$mcode]['cost'] = $value;
                                $methodArray[$method.'_'.$mcode] = $details[$mcode];
                            }
                        }
                    }
                }
                $shippingdata['methods'] = $methodArray;
                array_push($sellerMethods, $shippingdata);
            }

            if ($routeName == 'multishipping') {
                $addressBasedSellerPrice = [];
                $addressBasedSellerPrice = $this->_checkoutSession->getSellerMethod();
                $addressBasedSellerPrice[$request->getCustomAddressId()] = $sellerMethods;
                $this->_checkoutSession->setSellerMethod('');
                $this->_checkoutSession->setSellerMethod($addressBasedSellerPrice);
                
            } else {
                $this->_checkoutSession->setSellerMethod('');
                $this->_checkoutSession->setSellerMethod($sellerMethods);
            }
            
            $shippingamount=0;
            $carrierTitle = $this->getConfigData('title');
            if ($routeName == 'multishipping') {
                $customAmount = $this->_coreSession->getMultiAddressRates();
                if ($customAmount) {
                    foreach ($customAmount as $shippingRates) {
                        if ($shippingRates['customId'] == $request->getCustomAddressId()) {
                            if (!$enabledProductWiseMode) {
                                $shippingamount = array_sum($shippingRates['sellerRates']);
                                $this->mpMultiShipLog->critical("multishipping amount : ".$shippingamount);
                            } else {
                                foreach ($shippingRates['sellerRates'] as $sellerId => $shipmentPrice) {
                                    $shippingamount = array_sum($shipmentPrice) + $shippingamount;
                                }
                            }
                        }
                    }
                }
            } else {
                $customAmount = $this->_coreSession->getSelectedAmount();
                if ($customAmount) {
                    $shippingamount = $customAmount;
                }
            }
            $result = $this->_rateResultFactory->create();
            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($carrierTitle);
            $rate->setMethod($this->_code);
            $rate->setMethodTitle($carrierTitle);
            $rate->setPrice($shippingamount);
            $result->append($rate);
            return $result;
        } catch (\Exception $e) {
            $this->mpMultiShipLog->critical("Error : ".$e->getMessage());
        }
    }

    /**
     * sets request in case of product wise shipping
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return void
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        try {
            $enabledProductWiseMode = $this->getMultiShippingMode();
            if ($enabledProductWiseMode) {
                $shippingdetail = [];
                foreach ($request->getAllItems() as $item) {
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        break;
                    }
                    $mpassignproductId = $this->_getAssignProduct($item);
                    $sellerId = $this->_getSellerId($mpassignproductId, $item->getProductId());
                    $weight = $this->_getItemWeight($item);
                    $itemPrice = $item->getRowTotal();
                    list($originPostcode, $originCountryId, $origRegionCode, $originCity) =
                        $this->_getSellerOrigin($sellerId);

                    $currentUrl = $this->urlInterface->getCurrentUrl();
                    if (strpos($currentUrl, '/multishipping/checkout/shippingPost/') !== false ||
                        strpos($currentUrl, '/multishipping/checkout/overview/') !== false
                    ) {
                        $itemId = $item->getQuoteItemId();
                    } else {
                        $itemId = $item->getId();
                    }

                    $itemId = $itemId ? $itemId : $item->getQuoteItemId();
                    $shippingdetail[$item->getProduct()->getId()] = [
                        'seller_id' => $sellerId,
                        'origin_postcode' => $originPostcode,
                        'origin_country_id' => $originCountryId,
                        'origin_region' => $origRegionCode,
                        'origin_city' => $originCity,
                        'items_weight' => $weight,
                        'total_amount'=> $itemPrice,
                        'product_name' => $item->getName(),
                        'qty' => $item->getQty(),
                        'item_id' => $itemId,
                        'price' => $item->getPrice()*$item->getQty(),
                    ];
                    $sellerProductDetails[$itemId][] = $item->getName().' x '.$item->getQty();
                }
            } else {
                return parent::setRequest($request);
            }

            $request->setSellerProductInfo($sellerProductDetails);

            if ($request->getShippingDetails()) {
                $shippingdetail = $request->getShippingDetails();
            }
            $request->setShippingDetails($shippingdetail);

            if ($request->getDestCountryId()) {
                $destCountry = $request->getDestCountryId();
            } else {
                $destCountry = self::USA_COUNTRY_ID;
            }

            $request->setDestCountryId($destCountry);

            if ($request->getDestPostcode()) {
                $request->setDestPostal($request->getDestPostcode());
            }
            $this->setRawRequest($request);
            
            return $this;
        } catch (\Exception $e) {
            $this->mpMultiShipLog->addInfo($e->getMessage());
            return $this->returnErrorFromConfig();
        }
    }

    /**
     * get multishipping mode activated from system config
     *
     * @return void
     */
    public function getMultiShippingMode()
    {
        $mode = $this->getConfigData('shipping_mode');
        if ($mode == 2) {
            return true;
        }
        return false;
    }

    /**
     * get objects of all marketplace compatible shipping methods
     *
     * @param array $activeCarriresList
     * @return array $objectArray
     */
    private function _getObjects($activeCarriresList)
    {
        $objectArray = [];
        foreach ($activeCarriresList as $carrier) {
            switch ($carrier['value']) {
                case 'mpups':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpups',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpUPSShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'marketplaceusps':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'marketplaceusps',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpUSPSShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'mpdhl':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpdhl',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpDHLShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'mpfedex':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpfedex',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpFedexShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'mpfixrate':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpfixrate',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpFixedRateshipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mparamex':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mparamex',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpAramexShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'mpfreeshipping':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpfreeshipping',
                            'value' => $this->_objectManager->create(
                                \Webkul\Mpfreeshipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'mppercountry':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mppercountry',
                            'value' => $this->_objectManager->create(
                                \Webkul\Mppercountryperproductshipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'webkulmpperproduct':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'webkulmpperproduct',
                            'value' => $this->_objectManager->create(
                                \Webkul\Mpperproductshipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;

                case 'webkulshipping':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'webkulshipping',
                            'value' => $this->_objectManager->create(
                                \Webkul\Mpshipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpauspost':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpauspost',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpAuspost\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpeasypost':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpeasypost',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpEasyPost\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpcorreios':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpcorreios',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpCorreiosShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpcanadapost':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpcanadapost',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpCanadapostShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpfrenet':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpfrenet',
                            'value' => $this->_objectManager->create(
                                \Webkul\MarketplaceFrenetShipping\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                case 'mpshipstation':
                    array_push(
                        $objectArray,
                        [
                            'code' => 'mpshipstation',
                            'value' => $this->_objectManager->create(
                                \Webkul\MpShipStation\Model\Carrier::class
                            ),
                        ]
                    );
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $objectArray;
    }
    
    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['mpmultishipping' => $this->getConfigData('name')];
    }
    
    /**
     * Get base currency rate
     *
     * @param string $code
     * @return float
     */
    protected function _getBaseCurrencyRate($code)
    {
        $baseCurrencyRate = $this->currencyFactory->load($code)
                                    ->getAnyRate($this->_storeManager->getStore()->getBaseCurrencyCode());
        return $baseCurrencyRate;
    }

    /**
     * checks if shipping label is available for the method
     */
    public function isShippingLabelsAvailable()
    {
        $orderId = $this->_currentHelper->getCurrentOrderId();
        $sellerId = $this->_currentHelper->getCurrentSellerId();
        $salesOrder = $this->mpSalesModel->getCollection()->addFieldToFilter('order_id', $orderId)
                                            ->addFieldToFilter('seller_id', $sellerId)
                                            ->getFirstItem();
        if (!$salesOrder->getMultishipMethod()) {
            return false;
        } else {
            $carrierObjCode = $salesOrder->getMultishipMethod();
            $carrierObjCode = substr($carrierObjCode, 0, strpos($carrierObjCode, "_"));
            $carrierObj = $this->_currentHelper->getCarrierObjectByCode($carrierObjCode);
            try {
                if (!is_object($carrierObj)) {
                    return false;
                } else {
                    return $carrierObj->isShippingLabelsAvailable();
                }
            } catch (\Exception $e) {
                return false;
            }
        }
    }
}
