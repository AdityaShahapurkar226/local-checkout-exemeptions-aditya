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
namespace Webkul\MpMultiShipping\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * MpMultiShipping data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_code = 'mpmultishipping';

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->request = $request;
        $this->shippingConfig = $shippingConfig;
        $this->_carrierFactory = $carrierFactory;
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
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
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/'.$this->_code.'/'.$field;

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }

    /**
     * check if label content exists then show print button
     * @param int  $orderId
     * @return boolean
     */
    public function getActiveCarriers()
    {
        $carriers = [];
        $config = $this->shippingConfig->getActiveCarriers();
        foreach ($config as $carrierCode => $_method) {
            $this->_code = $carrierCode;
            $carrierTitle = $this->getConfigData('title');
            if (!$carrierTitle) {
                $carrierTitle = $carrierCode;
            }
            if ((strpos($carrierCode, 'webkul') !== false ||
                strpos($carrierCode, 'marketplace') !== false ||
                strpos($carrierCode, 'mp') !== false) &&
                strpos($carrierCode, 'mpmultishipping') !== 0) {
                $carriers[] = ['value' => $carrierCode, 'label' => $carrierTitle];
            }
        }
        return $carriers;
    }

    /**
     * get order id from request
     *
     * @return int|string
     */
    public function getCurrentOrderId()
    {
        try {
            return $orderId = $this->request->getParam('id');
        } catch (\Exception $e) {
            return $orderId = "";
        }
    }

    /**
     * get current seller id from session
     *
     * @return int
     */
    public function getCurrentSellerId()
    {
        return $this->_customerSession->getCustomer()->getId();
    }

    /**
     * get carrier object by cde
     *
     * @param string $code
     * @return string
     */
    public function getCarrierObjectByCode($code)
    {
        try {
            return $this->_carrierFactory->createIfActive($code);
        } catch (\Exception $e) {
            return "";
        }
    }
}
