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
namespace Webkul\MpMultiShipping\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Shipping\Model\CarrierFactory;
use Webkul\MpMultiShipping\Helper\Data;

class Shipping implements ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;
    
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;
    
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $carrierFactory;
    
    /**
     * @var \Webkul\MpMultiShipping\Helper\Data
     */
    protected $helper;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        JsonSerializer $jsonSerializer,
        Yesno $yesNo,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        TaxHelper $taxHelper,
        CarrierFactory $carrierFactory,
        Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->yesNo = $yesNo;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->taxHelper = $taxHelper;
        $this->carrierFactory = $carrierFactory;
        $this->helper = $helper;
    }
    
    /**
     * Get Checkout Session
     *
     * @return object
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
    
    /**
     * Get Customer Session
     *
     * @return object
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    
    /**
     * Get JSON Serializer
     *
     * @return object
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }
    
    /**
     * Get Price Helper
     *
     * @return object
     */
    public function getPriceHelper()
    {
        return $this->priceHelper;
    }

    /**
     * Get Tax Helper
     *
     * @return object
     */
    public function getTaxHelper()
    {
        return $this->taxHelper;
    }
    
    /**
     * Get MpMultiShipping Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->customerSession->getCustomer();
    }

     /**
      * Retrieve information from carrier configuration.
      *
      * @param string $field
      *
      * @return void|false|string
      */
    public function getConfigData($field)
    {
        return $this->getHelper()->getConfigData($field);
    }

    /**
     * get yes no data
     *
     * @return array
     */
    public function yesNoData()
    {
        return $this->yesNo->toOptionArray();
    }

    /**
     * get allowed shipping methods by seller
     *
     * @return array
     */
    public function getAllowedShipping()
    {
        try {
            $allowedShipping = $this->jsonSerializer->unserialize(
                $this->_getCustomerData()->getAllowedShipping(),
                true
            );
            if (!empty($allowedShipping)) {
                return $allowedShipping;
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * returns current currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * returns shipping method name from submethod anme
     *
     * @param string $subMethodCode
     * @return string
     */
    public function getShippingMethodFromSubMethod($subMethodCode)
    {
        try {
            if (!empty($subMethodCode)) {
                $shippingMethodCode = substr($subMethodCode, 0, strpos($subMethodCode, "_"));
                $carrierModel = $this->carrierFactory->createIfActive($shippingMethodCode);
                if (is_object($carrierModel)) {
                    return $carrierModel->getConfigData('title');
                } else {
                    return "";
                }
            } else {
                return "";
            }
            
        } catch (\Exception $e) {
            return "";
        } catch (\Eception $e) {
            return "";
        }
    }

    /**
     * checks if product wise shipping has been enabled
     *
     * @return bool
     */
    public function enabledProductWiseShipping()
    {
        try {
            $mode = $this->getConfigData('shipping_mode');
            if ($mode == 2) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * checks label available on order item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function checkLabelAvailableOnItem($item)
    {
        $carrierCode = $item->getShippingMethod();
        if ($carrierCode) {
            $carrierCode = substr($carrierCode, 0, strpos($carrierCode, "_"));
            $carrierObject = $this->helper->getCarrierObjectByCode($carrierCode);
        }
        
        if (isset($carrierObject) && is_object($carrierObject)) {
            return $carrierObject->isShippingLabelsAvailable();
        } else {
            return false;
        }
    }

    /**
     * fetch Marketplace Carriers
     *
     * @return array
     */
    public function marketplaceCarriers()
    {
        $carriersList = [
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
            'mpcanadapost',
            'webkulmpperproduct',
            'mparamex',
            'mpfrenet',
            'mpshipstation'
        ];

        return $carriersList;
    }
}
