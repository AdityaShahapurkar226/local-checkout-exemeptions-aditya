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
namespace Webkul\MpMultiShipping\Block;

class ManageMultiShipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpMultiShipping\Helper\Data
     */
    private $currentHelper;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesNo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Webkul\MpMultiShipping\Helper\Data $currentHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Webkul\MpMultiShipping\Helper\Data $currentHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->currentHelper = $currentHelper;
        $this->_storeManager = $storeManager;
        $this->carrierFactory = $carrierFactory;
        $this->customerSession = $customerSession;
        $this->yesNo = $yesNo;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
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
     * get current module helper.
     *
     * @return \Webkul\MpMultiShipping\Helper\Data
     */
    public function getHelper()
    {
        return $this->currentHelper;
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
     * Get Currency Code for Custom Value
     *
     * @return string
     */
    public function getCustomValueCurrencyCode()
    {
        $orderInfo = $this->getOrder();
        return $orderInfo->getBaseCurrency()->getCurrencyCode();
    }

    /**
     * get allowed shipping methods by seller
     *
     * @return array
     */
    public function getAllowedShipping()
    {
        try {
            $allowedShipping = $this->jsonHelper->jsonDecode($this->_getCustomerData()->getAllowedShipping(), true);
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
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
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
            $shippingMethodCode = substr($subMethodCode, 0, strpos($subMethodCode, "_"));
            $carrierModel = $this->carrierFactory->createIfActive($shippingMethodCode);
            if (is_object($carrierModel)) {
                return $carrierModel->getConfigData('title');
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
        $carrierCode = substr($carrierCode, 0, strpos($carrierCode, "_"));
        $carrierObject = $this->currentHelper->getCarrierObjectByCode($carrierCode);
        if (is_object($carrierObject)) {
            return $carrierObject->isShippingLabelsAvailable();
        } else {
            return false;
        }
    }
}
