<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrierOnline as CoreAbstractCarrierOnline;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Xml\Security;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Magento\Framework\Module\Dir;
use Magento\Dhl\Model\AbstractDhl;

/**
 * Marketplace Abstract online shipping carrier model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCarrierOnline extends CoreAbstractCarrierOnline
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code;

    /**
     *
     * @var null
     */
    protected $_rawRequest = null;

    protected $_request;

    /**
     *
     * @var null||\Magento\Sales\Model\Order
     */
    protected $_order = null;

     /**
      * @var \Magento\Framework\Session\SessionManager
      */
    protected $_coreSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_region;

    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;

    /**
     * @var array
     */
    protected $_serviceCodeToActualNameMap = [];

    /**
     * @var array
     */
    protected $_costArr = [];

    /**
     * @var array
     */
    protected $_totalPriceArr = [];

    /**
     * @var bool
     */
    protected $_check = false;

    /**
     * @var bool
     */
    protected $_flag = false;

    protected $marketplaceOrderHelper;

    protected $marketplaceProductFactory;

    protected $assignItemsFactory;

    protected $saleslistFactory;

    protected $productFactory;

    protected $addressFactory;

    protected $customerFactory;

    protected $quoteOptionFactory;

    protected $_carrierHelper;

    protected $requestInterface;

    protected $_httpClientFactory;

    protected $_countryParams;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var $logger
     */
    private $logger;

    /**
     * @var $checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Modeprotected $quoteOptionFactory;l\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Webkul\Marketplace\Helper\Orders $marketplaceOrderHelper
     * @param \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignItemsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $requestParam
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $quoteOptionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Module\Manager $moduleManager,
     * @param CarrierHelper $carrierHelper
     * @param LabelGenerator $labelGenerator
     * @param SessionManager $coreSession
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Webkul\Marketplace\Helper\Orders $marketplaceOrderHelper,
        \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        ShippingSettingRepository $shippingSettingRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $requestParam,
        \Magento\Quote\Model\Quote\Item\OptionFactory $quoteOptionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        CarrierHelper $carrierHelper,
        LabelGenerator $labelGenerator,
        SessionManager $coreSession,
        array $data = [],
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface = null,
        \Magento\Checkout\Model\Session $checkoutSession = null
    ) {
        $this->marketplaceOrderHelper = $marketplaceOrderHelper;
        $this->marketplaceProductFactory = $marketplaceProductFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->productFactory = $productFactory;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->quoteOptionFactory = $quoteOptionFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->storeManager = $storeManager;
        $this->requestInterface = $requestInterface;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_labelGenerator = $labelGenerator;
        $this->_coreSession = $coreSession;
        $this->shippingSettingRepository = $shippingSettingRepository;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $this->xmlSecurity = $xmlSecurity;
        $this->requestParam = $requestParam;
        $this->_region = $regionFactory;
        $this->serializerInterface = $serializerInterface
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\SerializerInterface::class
            );

        $this->checkoutSession = $checkoutSession
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Session::class
            );
    }

    /**
     * isMultiShippingActive
     */
    protected function isMultiShippingActive()
    {
        $routeName = $this->requestInterface->getRouteName();
        $moduleManager = ObjectManager::getInstance()->create(\Magento\Framework\Module\Manager::class);
        if ($routeName == 'multishipping' && ($moduleManager->isOutputEnabled("Webkul_MpMultiShipping") &&
        $this->_scopeConfig->getValue('carriers/mpmultishipping/active'))) {
            return true;
        } elseif ($moduleManager->isOutputEnabled("Webkul_MpMultiShipping") &&
        $this->_scopeConfig->getValue('carriers/mpmultishipping/active')) {
            return true;
        }
        return false;
    }

    /**
     * check is seller store pickup module active.
     * @return bool
     */
    protected function isStorePickupActive()
    {
        $routeName = $this->requestInterface->getRouteName();
        $moduleManager = ObjectManager::getInstance()->create(\Magento\Framework\Module\Manager::class);
        if ($routeName == 'wkpickup' && ($moduleManager->isOutputEnabled("Webkul_SellerStorePickup") &&
        $this->_scopeConfig->getValue('carriers/wkpickup/active'))) {
            return false;
        } elseif ($moduleManager->isOutputEnabled("Webkul_SellerStorePickup") &&
        $this->_scopeConfig->getValue('carriers/wkpickup/active')) {
            return true;
        }
        return false;
    }

    /**
     * Prepare and set request to this instance.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $mpassignproductId = 0;
        $shippingdetail = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $sellerId = 0;
            $mpassignproductId = $this->_getAssignProduct($item);
            $sellerId = $this->_getSellerId($mpassignproductId, $item->getProductId());
            $weight = $this->_getItemWeight($item);
            $itemPrice = $item->getBaseRowTotal();

            $collection = $item->getProduct()->getCollection()
                ->addAttributeToSelect(['ts_dimensions_width', 'ts_dimensions_height', 'ts_dimensions_length'])
                ->addFieldToFilter('entity_id', ['eq' => $item->getProductId()]);

            $length = '';
            $height = '';
            $width = '';

            foreach ($collection as $product) {
                $length = $product->getTsDimensionsLength();
                $height = $product->getTsDimensionsHeight();
                $width = $product->getTsDimensionsWidth();
            }

            list($originPostcode, $originCountryId, $origRegionCode, $originCity) = $this->_getSellerOrigin($sellerId);

            if ($this->_isUSCountry($originCountryId)) {
                list($originPostcode, $fromZip4) = $this->_parseZip($originPostcode);
            }

            $itemId = $item->getId();
            $req = $this->requestInterface;
            if ($req->getModuleName() == 'multishipping' && $req->getControllerName() == 'checkout') {
                $itemId = $item->getQuoteItemId();
            } elseif ($this->checkoutSession->getQuote()->getIsMultiShipping()) {
                $itemId = $item->getQuoteItemId();
            }

            if (empty($shippingdetail)) {
                array_push(
                    $shippingdetail,
                    [
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
                        'Dimensions' => [
                            [
                                'length' => $length,
                                'height' => $height,
                                'width'  => $width,
                            ]
                        ]

                    ]
                );
            } else {
                $shipinfoflag = true;
                $index = 0;
                foreach ($shippingdetail as $itemship) {
                    if ($itemship['seller_id'] == $sellerId) {
                        $itemship['items_weight'] = $itemship['items_weight'] + $weight;
                        $itemship['total_amount']= $itemship['total_amount']+$itemPrice;
                        $itemship['product_name'] = $itemship['product_name'].','.$item->getName();
                        $itemship['item_id'] = $itemship['item_id'].','.$itemId;
                        $itemship['qty'] = $itemship['qty'] + $item->getQty();
                        $itemship['price'] = $itemship['price'] + $item->getPrice()*$item->getQty();
                        $itemship['Dimensions'][] = [
                                                       'length' =>$length,
                                                       'height' =>$height,
                                                       'width'  =>$width
                                                    ];
                        $shippingdetail[$index] = $itemship;
                        $shipinfoflag = false;
                    }
                    ++$index;
                }
                if ($shipinfoflag == true) {
                    array_push(
                        $shippingdetail,
                        [
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
                            'Dimensions' => [
                                [
                                    'length' => $length,
                                    'height' => $height,
                                    'width'  => $width,
                                ]
                            ]

                        ]
                    );
                }
            }
        }

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
    }
    /**
     * Is Marketplace Shipping Method Method.
     * @return boolean
     */
    protected function _isShippingMethod()
    {
        $shippingmethod = $this->_rawRequest->getShippingMethod();

        $helper = $this->marketplaceOrderHelper;
        $orderInfo = $helper->getOrderinfo($this->_rawRequest->getId());
        $multishipping = [];
        if (strrpos($shippingmethod, 'mpmultishipping') !== false) {
            $multishipping = explode('_', $shippingmethod);
        }
        if (strpos($shippingmethod, $this->_code) !== false ||
            (isset($multishipping[0]) && $multishipping[0] == $this->_code)
        ) {
            return true;
        }
        return false;
    }
    /**
     * return service type for shipment.
     *
     * @return string
     */
    protected function _getServiceCode()
    {
        $shippingmethod = explode($this->_code.'_', $this->_order->getShippingMethod());
        $multishipping = $this->_order->getShippingMethod();
        $helper = $this->marketplaceOrderHelper;
        $orderInfo = $helper->getOrderinfo($this->_order->getId());
        if (strrpos($multishipping, 'mpmultishipping') !== false) {
            $shippingmethod = explode($this->_code.'_', $orderInfo->getMultishipMethod());
        }
        return $shippingmethod[1];
    }

    /**
     * Retunr Package weight for shipment
     *
     * @return int
     */
    protected function _getPackageWeight()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $orderedItems = $this->_order->getAllItems();
        $orderId = $this->_order->getId();
        $weight = 0;
        if ($customerId) {
            foreach ($orderedItems as $_item) {
                $sellerOrderslist = $this->saleslistFactory->create()->getCollection()
                        ->addFieldToFilter('seller_id', ['eq' => $customerId])
                        ->addFieldToFilter('order_id', ['eq' => $orderId])
                        ->addFieldToFilter('mageproduct_id', ['eq' => $_item->getProductId()])
                        ->addFieldToFilter('order_item_id', ['eq' => $_item->getItemId()]);
                $product = $this->getProductModel()->load($_item->getProductId());
                if (count($sellerOrderslist) > 0) {
                    $weight = $weight + $product->getWeight() * $_item->getQtyOrdered();
                }
            }
        } else {
            if ($this->requestParam->getParam('packages')) {
                foreach ($this->requestParam->getParam('packages') as $package) {
                    $weight = $weight + $package['params']['weight'];
                }
            }
        }

        return $weight;
    }
    /**
     * get seller id.
     *
     * @param int $mpassignproductId
     * @param int $proid
     *
     * @return int
     */
    protected function _getSellerId($mpassignproductId, $proid)
    {
        $sellerId = 0;
        if ($mpassignproductId) {
            $this->assignItemsFactory = ObjectManager::getInstance()->create(
                \Webkul\MpAssignProduct\Model\ItemsFactory::class
            );
            $mpassignModel = $this->assignItemsFactory->create()->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $collection = $this->marketplaceProductFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('mageproduct_id', ['eq' => $proid]);
            foreach ($collection as $temp) {
                $sellerId = $temp->getSellerId();
            }
        }

        return $sellerId;
    }
    /**
     * get product weight.
     *
     * @param object $item
     *
     * @return int
     */
    protected function _getItemWeight($item)
    {
        $weight = 0;
        if ($item->getHasChildren()) {
            $childWeight = 0;
            foreach ($item->getChildren() as $child) {
                if ($child->getProduct()->isVirtual()) {
                    continue;
                }
                $productWeight = $this->getProductModel()->load(
                    $child->getProductId()
                )->getWeight();
                $childWeight += $productWeight * $child->getQty();
            }
            $weight = $childWeight * $item->getQty();
        } else {
            $productWeight = $this->getProductModel()->load(
                $item->getProductId()
            )->getWeight();

            $weight = $productWeight * $item->getQty();
            if ($item->getQtyOrdered()) {
                $weight = $productWeight * $item->getQtyOrdered();
            }
        }
        return $weight;
    }
    /**
     * get assign product id.
     *
     * @param object $item
     *
     * @return int
     */
    protected function _getAssignProduct($item)
    {
        $mpassignproductId = 0;
        $itemOption = $this->quoteOptionFactory->create()
            ->getCollection();

        $itemId = $item->getId();
        $req = $this->requestInterface;
        if ($req->getModuleName() == 'multishipping' && $req->getControllerName() == 'checkout') {
            $itemId = $item->getQuoteItemId();
        }

        $itemOption = $itemOption->addFieldToFilter('item_id', ['eq' => $itemId])
            ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
        $optionValue = '';

        if ($itemOption->getSize()) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        if ($optionValue != '') {
            $temp = [];
            if ($this->_validJson($optionValue)) {
                $temp = json_decode($optionValue, true);
            } else {
                $temp = $this->serializerInterface->unserialize($optionValue);
            }
            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
        }

        return $mpassignproductId;
    }

    /**
     * Set seller origin address to request
     *
     * @param int $sellerId
     * @return void
     */
    protected function _setOriginAddress($sellerId)
    {
        $request = $this->_rawRequest;
        if ($sellerId) {
            $address = $this->shippingSettingRepository->getBySellerId($sellerId);

            $request->setOriginPostcode($address->getPostalCode());
            $request->setOriginCountryId($address->getCountryId());
            $request->setOriginCity($address->getCity());
            $region = $this->_region->create()->load($address->getRegionId())->getCode();
            if ($region != '') {
                $request->setOrigState($region);
            } else {
                $request->setOrigState($address->getCountryId());
            }
        } else {
            $request->setOriginPostcode(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()->getStoreId()
                )
            );
            $request->setOriginCountryId(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()->getStoreId()
                )
            );
            $request->setOrigState(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()->getStoreId()
                )
            );
            $request->setOriginCity(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->storeManager->getStore()->getStoreId()
                )
            );
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $sellerId
     * @return void
     */
    protected function _getSellerOrigin($sellerId)
    {
        $originCity = '';
        if ($sellerId) {
            $address = $this->shippingSettingRepository->getBySellerId($sellerId);
            $originPostcode = $address->getPostalCode();
            $originCountryId = $address->getCountryId();
            $originRegion = $address->getRegionId();
            $originCity = $address->getCity();
        } else {
            $originPostcode = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getStoreId()
            );
            $originCountryId = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getStoreId()
            );
            $originRegion = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getStoreId()
            );
            $originCity = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getStoreId()
            );
        }
        if (is_numeric($originRegion)) {
            $originRegion = $this->_regionFactory->create()->load($originRegion)->getCode();
        }

        return [$originPostcode, $originCountryId, $originRegion, $originCity];
    }

    /**
     * set shipping information
     * @param array $shippingInfo
     * @return void
     */
    public function setShippingInformation($shippingInfo)
    {
        if ($this->requestInterface->getModuleName() == 'multishipping' &&
            $this->requestInterface->getControllerName() == 'checkout'
        ) {
            $shippingCode = array_keys($shippingInfo)[0];
            $sessionInfo = $this->_customerSession->getShippingInformation();

            $addrSequence = (int) $this->_customerSession->getAddressSequence();

            if (isset($sessionInfo[$shippingCode])) {
                foreach ($shippingInfo[$shippingCode] as $shipData) {
                    $shipData['address_sequence'] = $addrSequence;
                    if (array_search($shipData, $sessionInfo[$shippingCode]) === false) {
                        $sessionInfo[$shippingCode][] = $shipData;
                    }
                }
            } else {
                $shippingInfo[$shippingCode][0]['address_sequence'] = $addrSequence;
                $sessionInfo[$shippingCode] = $shippingInfo[$shippingCode];
            }

            $this->_customerSession->setShippingInformation($sessionInfo);
        } else {
            $this->_customerSession->setShippingInformation($shippingInfo);
        }
    }

    /**
     * Validates JSON
     */
    private function _validJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomerModel()
    {
        return $this->customerFactory->create();
    }

    /**
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductModel()
    {
        return $this->productFactory->create();
    }

    protected function _filterSellerRate($priceArr)
    {
        if (count($this->_totalPriceArr) > 0) {
            foreach ($priceArr as $method => $price) {
                if (array_key_exists($method, $this->_totalPriceArr)) {
                    $this->_check = true;
                    $this->_totalPriceArr[$method]= $this->_totalPriceArr[$method]+$priceArr[$method];
                } else {
                    unset($priceArr[$method]);
                    $this->_flag = $this->_check==true?false:true;
                }
            }
        } else {
            $this->_totalPriceArr=$priceArr;
        }

        if ($priceArr && count($priceArr) > 0) {
            foreach ($this->_totalPriceArr as $method => $price) {
                if (!array_key_exists($method, $priceArr)) {
                    unset($this->_totalPriceArr[$method]);
                }
            }
        } else {
            $this->_totalPriceArr = [];
            $this->_flag = true;
        }

        return $this->_flag;
    }

    /**
     * Parse zip from string to zip5-zip4
     *
     * @param string $zipString
     * @param bool $returnFull
     * @return string[]
     */
    protected function _parseZip($zipString, $returnFull = false)
    {
        $zip4 = '';
        $zip5 = '';
        $zip = [$zipString];
        if (preg_match('/[\\d\\w]{5}\\-[\\d\\w]{4}/', $zipString) != 0) {
            $zip = explode('-', $zipString);
        }
        $count = count($zip);
        for ($i = 0; $i < $count; ++$i) {
            if (strlen($zip[$i]) == 5) {
                $zip5 = $zip[$i];
            } elseif (strlen($zip[$i]) == 4) {
                $zip4 = $zip[$i];
            }
        }
        if (empty($zip5) && empty($zip4) && $returnFull) {
            $zip5 = $zipString;
        }

        return [$zip5, $zip4];
    }
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'unit_of_measure' => ['L' => __('Pounds'), 'K' => __('Kilograms')],
            'unit_of_dimension' => ['I' => __('Inches'), 'C' => __('Centimeters')],
            'unit_of_dimension_cut' => ['I' => __('inch'), 'C' => __('cm')],
            'dimensions' => ['HEIGHT' => __('Height'), 'DEPTH' => __('Depth'), 'WIDTH' => __('Width')],
            'size' => ['0' => __('Regular'), '1' => __('Specific')],
            'dimensions_variables' => [
                'L' => \Zend_Measure_Weight::POUND,
                'LB' => \Zend_Measure_Weight::POUND,
                'POUND' => \Zend_Measure_Weight::POUND,
                'K' => \Zend_Measure_Weight::KILOGRAM,
                'KG' => \Zend_Measure_Weight::KILOGRAM,
                'KILOGRAM' => \Zend_Measure_Weight::KILOGRAM,
                'I' => \Zend_Measure_Length::INCH,
                'IN' => \Zend_Measure_Length::INCH,
                'INCH' => \Zend_Measure_Length::INCH,
                'C' => \Zend_Measure_Length::CENTIMETER,
                'CM' => \Zend_Measure_Length::CENTIMETER,
                'CENTIMETER' => \Zend_Measure_Length::CENTIMETER,
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        $code = strtoupper($code);
        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Return Country Name
     *
     * @param string $countryCode
     * @return void
     */
    public function _getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
    /**
     * Get shipping date
     *
     * @return string
     */
    protected function _getShipDate()
    {
        return $this->_determineShippingDay(
            $this->getConfigData('shipment_days'),
            date(AbstractDhl::REQUEST_DATE_FORMAT)
        );
    }

    /**
     * Determine shipping day according to configuration settings
     *
     * @param string[] $shippingDays
     * @param string $date
     * @return string
     */
    protected function _determineShippingDay($shippingDays, $date)
    {
        if (empty($shippingDays)) {
            return $date;
        }

        $shippingDays = explode(',', $shippingDays);

        $i = -1;
        do {
            $i++;
            $weekday = date('D', strtotime("{$date} +{$i} day"));
        } while (!in_array($weekday, $shippingDays) && $i < 10);

        return date(AbstractDhl::REQUEST_DATE_FORMAT, strtotime("{$date} +{$i} day"));
    }

    /**
     * Get Country Params by Country Code
     *
     * @param string $countryCode
     * @return \Magento\Framework\DataObject
     *
     * @see $countryCode ISO 3166 Codes (Countries) A2
     */
    protected function getCountryParams($countryCode)
    {
        if (empty($this->_countryParams)) {
            $configReader = ObjectManager::getInstance()->create(\Magento\Framework\Module\Dir\Reader::class);
            $readFactory = ObjectManager::getInstance()->create(
                \Magento\Framework\Filesystem\Directory\ReadFactory::class
            );
            $etcPath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Dhl');
            $directoryRead = $readFactory->create($etcPath);
            $countriesXml = $directoryRead->readFile('countries.xml');
            $this->_countryParams = $this->_xmlElFactory->create(['data' => $countriesXml]);
        }
        if (isset($this->_countryParams->{$countryCode})) {
            $countryParams = new \Magento\Framework\DataObject($this->_countryParams->{$countryCode}->asArray());
        }

        return isset($countryParams) ? $countryParams : new \Magento\Framework\DataObject();
    }

    /**
     * Check if shipping is domestic
     *
     * @param string $originCountryCode
     * @param string $destinationCountryCode
     * @return bool
     */
    protected function _checkDomesticStatus($originCountryCode, $destinationCountryCode)
    {
        $isDomestic = false;

        $orignCountry = (string)$this->getCountryParams($originCountryCode)->getData('name');
        $destinationCountry = (string)$this->getCountryParams($destinationCountryCode)->getData('name');
        $isDomestic = (string)$this->getCountryParams($destinationCountryCode)->getData('domestic');

        if (($orignCountry == $destinationCountry && $isDomestic)
            || ($this->_carrierHelper->isCountryInEU($originCountryCode)
                && $this->_carrierHelper->isCountryInEU($destinationCountryCode)
            )
        ) {
            $isDomestic = true;
        }

        return $isDomestic;
    }

    /**
     * Clean service name from unsupported strings and characters
     *
     * @param  string $name
     * @return string
     */
    //@codingStandardsIgnoreStart
    protected function _filterServiceName($name)
    {
        $name = (string)preg_replace(
            ['~<[^/!][^>]+>.*</[^>]+>~sU', '~\<!--.*--\>~isU', '~<[^>]+>~is'],
            '',
            html_entity_decode($name)
        );
        $name = str_replace('*', '', $name);

        return $name;
    }
    //@codingStandardsIgnoreEnd

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * Returns error messge at the checkout page when the rates are not available
     *
     * @return object
     */
    public function returnErrorFromConfig()
    {
        $result = $this->_rateFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg'));
        return $result->append($error);
    }
}
