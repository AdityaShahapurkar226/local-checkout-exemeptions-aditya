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

use Magento\Shipping\Model\Carrier\AbstractCarrier as CoreAbstractCarrier;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Magento\Framework\App\ObjectManager;

/**
 * Marketplace Abstract shipping carrier model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCarrier extends CoreAbstractCarrier
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code;

    /**
     * Rate request data.
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Raw rate request data.
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;

    protected $_currency;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /** @var \Magento\Directory\Model\RegionFactory */
    protected $regionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $marketplaceProductFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerInterface;

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param SessionManager $coreSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param PriceCurrencyInterface $priceCurrency
     * @param OptionFactory $quoteOptionFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param ShippingSettingRepository $shippingSettingRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $requestInterface,
        PriceCurrencyInterface $priceCurrency,
        OptionFactory $quoteOptionFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \Webkul\Marketplace\Model\ProductFactory $marketplaceProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        ShippingSettingRepository $shippingSettingRepository,
        array $data = [],
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface = null
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->_coreSession = $coreSession;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_regionFactory = $regionFactory;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_localeFormat = $localeFormat;
        $this->jsonHelper = $jsonHelper;
        $this->requestInterface = $requestInterface;
        $this->priceCurrency = $priceCurrency;
        $this->quoteOptionFactory = $quoteOptionFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->marketplaceProductFactory = $marketplaceProductFactory;
        $this->productFactory = $productFactory;
        $this->shippingSettingRepository = $shippingSettingRepository;
        $this->saleslistFactory = $saleslistFactory;
        $this->serializerInterface = $serializerInterface
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\SerializerInterface::class
            );
    }

    /**
     * @param \Magento\Framework\DataObject|null $request
     *
     * @return $this
     *
     * @api
     */
    public function setRawRequest($request)
    {
        $this->_rawRequest = $request;

        return $this;
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
        $sellerProductDetails = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $sellerId = 0;
            $mpassignproductId = $this->_getAssignProduct($item);
            $sellerId = $this->_getSellerId($mpassignproductId, $item->getProductId());
            $weight = $this->_getItemWeight($item);
            $itemPrice = $item->getBaseRowTotal();
            list($originPostcode, $originCountryId, $origRegionCode, $originCity) = $this->_getSellerOrigin($sellerId);

            $itemId = $item->getId();
            $req = $this->requestInterface;
            if ($req->getModuleName() == 'multishipping' && $req->getControllerName() == 'checkout') {
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
                    ]
                );
                $sellerProductDetails[$sellerId][] = $item->getName().' x '.$item->getQty();
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
                        $shippingdetail[$index] = $itemship;
                        $shipinfoflag = false;
                        $sellerProductDetails[$sellerId][] = $item->getName().' X '.$item->getQty();
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
                        ]
                    );
                    $sellerProductDetails[$sellerId][] = $item->getName().' X '.$item->getQty();
                }
            }
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

            if (isset($sessionInfo[$shippingCode])) {
                foreach ($shippingInfo[$shippingCode] as $shipData) {
                    if (array_search($shipData, $sessionInfo[$shippingCode]) === false) {
                        $sessionInfo[$shippingCode][] = $shipData;
                    }
                }
            } else {
                $sessionInfo[$shippingCode] = $shippingInfo[$shippingCode];
            }

            $this->_customerSession->setShippingInformation($sessionInfo);
        } else {
            $this->_customerSession->setShippingInformation($shippingInfo);
        }
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
                    $this->_storeManager->getStore()->getStoreId()
                )
            );
            $request->setOriginCountryId(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->_storeManager->getStore()->getStoreId()
                )
            );
            $request->setOrigState(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->_storeManager->getStore()->getStoreId()
                )
            );
            $request->setOriginCity(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->_storeManager->getStore()->getStoreId()
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
                $this->_storeManager->getStore()->getStoreId()
            );
            $originCountryId = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getStoreId()
            );
            $originRegion = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getStoreId()
            );
            $originCity = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()->getStoreId()
            );
        }
        if (is_numeric($originRegion)) {
            $originRegion = $this->_regionFactory->create()->load($originRegion)->getCode();
        }

        return [$originPostcode, $originCountryId, $originRegion, $originCity];
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
     * Returns error messge at the checkout page when the rates are not available
     *
     * @return object
     */
    public function returnErrorFromConfig()
    {
        $result = $this->_rateResultFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg'));
        return $result->append($error);
    }
}
