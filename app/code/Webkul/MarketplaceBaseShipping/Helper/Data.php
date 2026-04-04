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
 
namespace Webkul\MarketplaceBaseShipping\Helper;

/**
 * MarketplaceBaseShipping data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var string
     */

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Framework\Locale\CurrencyInterface  $localeCurrency
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
     */
    public function __construct(
        \Webkul\MarketplaceBaseShipping\Logger\Logger $logger,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Model\OrdersFactory $marketplaceOrderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
    ) {
        $this->logger = $logger;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_mpHelper = $mpHelper;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->marketplaceOrderFactory = $marketplaceOrderFactory;
        $this->customerFactory = $customerFactory;
        $this->invoiceFactory = $invoiceFactory;
    }

    public function displayErrors()
    {
        return $this->logger;
    }

    public function isShippingLabelCreated($shipmentId)
    {
        $mpOrder = $this->marketplaceOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $this->_customerSession->getCustomerId()])
            ->addFieldToFilter('shipment_id', ['eq' => $shipmentId]);

        if ($mpOrder->getSize()) {
            $data = $mpOrder->getFirstItem();
            if ($data->getShipmentLabel()) {
                return true;
            }
        }
        return false;
    }

    /**
     * get carrier code
     *
     * @return void
     */
    public function getCarrierCode($order)
    {
        $sellerOrders = $this->marketplaceOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $this->_customerSession->getCustomerId()])
            ->addFieldToFilter('order_id', ['eq' => $order->getId()]);

        $orderShipmentCode = $order->getShippingMethod(true)->getCarrierCode();

        if ($sellerOrders->getSize() && $this->isMultiShippingActive() &&
        strpos($orderShipmentCode, "mpmultishipping") !== -1) {
            $dataModel = $sellerOrders->getFirstItem();
            $multiShipMethod = $dataModel->getMultishipMethod();
            $method = explode('_', $multiShipMethod);
            if (isset($method[0])) {
                return $method[0];
            }
        }
        return $order->getShippingMethod(true)->getCarrierCode();
    }

    /**
     * isMultiShippingActive
     */
    protected function isMultiShippingActive()
    {
        if ($this->_moduleManager->isOutputEnabled("Webkul_MpMultiShipping") &&
        $this->_scopeConfig->getValue('carriers/mpmultishipping/active')) {
            return true;
        }
        return false;
    }

    /**
     * get carrier code
     *
     * @return void
     */
    public function getShippingMethod($order)
    {
        $sellerOrders = $this->marketplaceOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $this->_customerSession->getCustomerId()])
            ->addFieldToFilter('order_id', ['eq' => $order->getId()]);

        if ($sellerOrders->getSize() && $this->isMultiShippingActive()) {
            $dataModel = $sellerOrders->getFirstItem();
            $multiShipMethod = $dataModel->getMultishipMethod();
            $method = explode('_', $multiShipMethod);
            $count = count($method);
            for ($i=1; $i<$count; $i++) {
                if (empty($methodName)) {
                    $methodName = $method[$i];
                } else {
                    $methodName .= "_".$method[$i];
                }
            }
            $response = new \Magento\Framework\DataObject;
            $response->setCarrierCode($method[0]);
            $response->setMethod($methodName);
            return $response;
        }
        return $order->getShippingMethod(true);
    }

    /**
     * returns weight unit according to current store
     * @return string
     */
    public function getDimensionsUnit()
    {
        $weightUom = $this->_mpHelper->getWeightUnit();
        return $dimensionsUom = ($weightUom === 'kgs') ? 'cm' : 'in';
    }
}
