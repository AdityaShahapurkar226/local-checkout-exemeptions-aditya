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

namespace Webkul\MarketplaceBaseShipping\Block\Order\Packaging;

class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/packaging/grid.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ItemFactory
     */
    protected $_shipmentItemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository $dataRepository,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_coreRegistry = $registry;
        $this->dataRepository = $dataRepository;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Return collection of shipment items
     *
     * @return array
     */
    public function getCollection()
    {
        if ($this->getShipment()->getId()) {
            $collection = $this->_shipmentItemFactory->create()->getCollection()->setShipmentFilter(
                $this->getShipment()->getId()
            );
        } else {
            $collection = $this->getShipment()->getAllItems();
        }
            
        return $collection;
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        $storeId = $this->getShipment()->getStoreId();
        $order = $this->getShipment()->getOrder();
        $address = $order->getShippingAddress();
        $customerId = $this->_customerSession->getCustomerId();
        $shipperAddress = $this->dataRepository->getBySellerId($customerId, $storeId);
        
        $shipperAddressCountryCode = $shipperAddress->getCountryId();
        $recipientAddressCountryCode = $address->getCountryId();
        if ($shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Format price
     *
     * @param   float $value
     * @return  string
     */
    public function formatPrice($value)
    {
        return sprintf('%.2F', $value);
    }
}
