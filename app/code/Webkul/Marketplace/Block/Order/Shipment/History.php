<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block\Order\Shipment;

/**
 * Webkul Marketplace Order Shipment History Block.
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order\Shipment;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    protected $ordersHelper;

    /**
     * @var Shipment
     */
    protected $shipmentModel;

    /**
     * @var Collection
     */
    protected $saleslistCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Construct
     *
     * @param Order $order
     * @param Customer $customer
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Orders $ordersHelper
     * @param Shipment $shipmentModel
     * @param Collection $saleslistCollection
     * @param array $data
     */
    public function __construct(
        Order $order,
        Customer $customer,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        Shipment $shipmentModel,
        Collection $saleslistCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_customerSession = $customerSession;
        $this->ordersHelper = $ordersHelper;
        $this->shipmentModel = $shipmentModel;
        $this->saleslistCollection = $saleslistCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance.
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Set title
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Shipments'));
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * Get collection
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment\Collection
     */

    public function getCollection()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $tracking = $this->ordersHelper->getOrderinfo($orderId);
        $shipments = [];
        if ($tracking) {
            $shipmentIds = [];
            $shipmentIds = explode(',', $tracking->getShipmentId());
            $shipments = $this->shipmentModel->getCollection()
                          ->addFieldToFilter(
                              'entity_id',
                              ['in' => $shipmentIds]
                          );
        }
        return $shipments;
    }

    /**
     * Set collection
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.order.shipment.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * Get Pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}
