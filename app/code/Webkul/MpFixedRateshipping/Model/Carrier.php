<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpFixedRateshipping
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpFixedRateshipping\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Magento\Framework\UrlInterface as UrlInterface;

/**
 * Marketplace Percountry Perproduct shipping.
 */
class Carrier extends \Webkul\MarketplaceBaseShipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    public const CODE = 'mpfixrate';

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
     * @var \Webkul\MpFixedRateshipping\Helper\Data
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

    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;

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
     * @param \Webkul\MpFixedRateshipping\Helper\Data $currentHelper
     * @param UrlInterface $urlInterface
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
        \Webkul\MpFixedRateshipping\Helper\Data $currentHelper,
        UrlInterface $urlInterface,
        array $data = []
    ) {
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
            $quoteOptionFactory,
            $customerFactory,
            $addressFactory,
            $marketplaceProductFactory,
            $productFactory,
            $saleslistFactory,
            $shippingSettingRepository,
            $data
        );
        $this->_currentHelper = $currentHelper;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || $this->isMultiShippingActive()) {
            return false;
        }

        $this->setRequest($request);
        $shippingpricedetail = $this->getShippingPricedetail($this->_rawRequest, $request);
        $result = $this->_rateResultFactory->create();
        if (isset($shippingpricedetail['error']['error']) && $shippingpricedetail['error']['error'] == 1) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('mpfixrate');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier('mpfixrate');
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod('mpfixrate');
            $rate->setMethodTitle($this->getConfigData('method_title'));
            $rate->setCost($shippingpricedetail['handlingfee']);
            $rate->setPrice($shippingpricedetail['handlingfee']);
            $result->append($rate);
        }

        return $result;
    }

    /**
     * Calculate the rate according to fix rate set by the seller.
     *
     * @param \Magento\Framework\DataObject $request
     * @param object $rateRequest
     * @return Result
     */
    public function getShippingPricedetail(\Magento\Framework\DataObject $request, $rateRequest = null)
    {
        $sellerProductQty = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $itemId = $item->getId();
            $sellerProductQty[$itemId] = $item->getQty();
        }

        $currentUrl = $this->urlInterface->getCurrentUrl();
        if (strpos($currentUrl, '/multishipping/checkout/') !== false && $rateRequest != null) {
            foreach ($rateRequest->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    break;
                }
                $itemId = $item->getQuoteItemId();
                $sellerProductQty[$itemId] = $item->getQty();
            }
        }

        if ($this->isMultiShippingActive()) {
            $multiBasedOn = $this->_currentHelper->getConfigData('shipping_mode', 'mpmultishipping');
            $fixBasedOn = $this->_currentHelper->getConfigData('shippingappliedon');
            if (($multiBasedOn == 2 && $fixBasedOn != 'product') || ($multiBasedOn == 1 && $fixBasedOn != 'vendor')) {
                $this->logger->addInfo("Rate Type (Product Wise/Seller Wise) should be same as in MultiShipping.");
                return false;
            }
        }
        $r = $request;
        $submethod = [];
        $shippinginfo = [];
        $handling = 0;
        foreach ($r->getShippingDetails() as $shipdetail) {
            $fixrateAmount = $this->_currentHelper->getShippingCharges($shipdetail['seller_id']) == '' ?
                $this->getConfigData('default_amount') :
                $this->_currentHelper->getShippingCharges($shipdetail['seller_id']);

            $freeShippingFrom = $this->_currentHelper->getFreeShippingFrom($shipdetail['seller_id']) == '' ?
                $this->getConfigData('shipping_up_to') :
                $this->_currentHelper->getFreeShippingFrom($shipdetail['seller_id']);

            $totalPrice = 0;
            $totalQty = 0;
            if (!isset($shipdetail['item_id_details'])) {
                $shipdetail['item_id_details'] = [];
            }
            if (!isset($shipdetail['item_name_details'])) {
                $shipdetail['item_name_details'] = [];
            }
            if (!isset($shipdetail['item_qty_details'])) {
                $shipdetail['item_qty_details'] = [];
            }
            if($shipdetail['item_id']){
                $itemsArray = explode(',', $shipdetail['item_id']);
            }else{
                $itemsArray = $shipdetail['item_id'];
            }
            $allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            $bundlePrice = 0;
            $nonShippingPro = 0;
            $itemsArray = []; // Initialize $itemsArray as an empty array

            foreach ($allItems as $item) {
                $productPrice = 0;
                $qty = 0;
                if (in_array($item->getId(), $itemsArray)) {
                    $productData = [];
                    $flag = 0;
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        $nonShippingPro++;
                        continue;
                    }
                    $productId = $item->getProductId();

                    $qty = $item->getQty();
                    $productPrice = $item->getPrice() * $sellerProductQty[$item->getId()];
                }
                $totalPrice = $totalPrice + $productPrice;
            }
            $totalQty = $shipdetail['qty'] - $nonShippingPro;

            $price = floatval($fixrateAmount) * $totalQty;
            if ($fixrateAmount == '') {
                $debugData['error'] = 1;
            }

            /*
                calculate fix rate shipping base on admin configuration
             */
            if ($this->getConfigData('shippingappliedon') != 'vendor') {
                if ($freeShippingFrom != '' && $freeShippingFrom <= $totalPrice) {
                    $price = 0;
                } else {
                    $handling = $handling + $price;
                }
            } else {
                if ($freeShippingFrom != '' && $freeShippingFrom <= $totalPrice) {
                    $price = 0;
                    $handling = $handling + 0;
                } else {
                    $price = $fixrateAmount;
                    $handling = $handling + $fixrateAmount;
                }
            }
            $itemPriceDetails = [];
            foreach ($allItems as $item) {
                $productPrice = 0;
                $qty = 0;
                if (in_array($item->getId(), $itemsArray)) {
                    $productData = [];
                    $flag = 0;
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }
                    $productId = $item->getProductId();
                    $qty = $sellerProductQty[$item->getId()];
                    $productPrice = $item->getPrice() * $qty;
                }
                $itemPrice = floatval($fixrateAmount) * $qty;
                if ($fixrateAmount == '') {
                    $debugData['error'] = 1;
                }

                /*
                 * calculate fix rate shipping base on admin configuration
                 */
                if (strpos($currentUrl, '/multishipping/checkout/') !== false) {
                    if ($this->getConfigData('shippingappliedon') != 'vendor') {
                        if ($freeShippingFrom != '' && $freeShippingFrom <= $productPrice) {
                            $itemPrice = 0;
                        }
                    }
                } elseif ($this->getConfigData('shippingappliedon') != 'vendor' &&
                    strpos($currentUrl, '/multishipping/checkout/') === false
                ) {
                    if ($freeShippingFrom != '' && $freeShippingFrom <= $totalPrice) {
                        $itemPrice = 0;
                    }
                } elseif ($this->getConfigData('shippingappliedon') == 'vendor') {
                    if ($freeShippingFrom != '' && $freeShippingFrom <= $totalPrice) {
                        $itemPrice = 0;
                    } else {
                        $itemPrice = $fixrateAmount;
                    }
                }
                if (in_array($item->getId(), $itemsArray)) {
                    $itemPriceDetails[$item->getId()] = $itemPrice;
                }
            }
            $submethod = [
                $this->_code => [
                    'method' => $this->getConfigData('title'),
                    'cost' => $price,
                    'base_amount' => floatval($price),
                    'error' => 0
                ]
            ];
            array_push(
                $shippinginfo,
                [
                    'seller_id' => $shipdetail['seller_id'],
                    'methodcode' => $this->_code,
                    'shipping_ammount' => $price,
                    'product_name' => $shipdetail['product_name'],
                    'submethod' => $submethod,
                    'item_ids' => $shipdetail['item_id'],
                    'item_price_details' => $itemPriceDetails,
                    'item_id_details' => $shipdetail['item_id_details'],
                    'item_name_details' => $shipdetail['item_name_details'],
                    'item_qty_details' => $shipdetail['item_qty_details']
                ]
            );
        }

        $debugData['result'] = $shippinginfo;
        $this->_debug($debugData);
        $result = ['handlingfee' => $handling, 'shippinginfo' => $shippinginfo, 'error' => $debugData];

        $shippingAll = [];
        $shippingAll[$this->_code] = $result['shippinginfo'];
        $this->setShippingInformation($shippingAll);

        return $result;
    }

    /**
     * Get Allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['mpfixrate' => $this->getConfigData('method_title')];
    }
}
