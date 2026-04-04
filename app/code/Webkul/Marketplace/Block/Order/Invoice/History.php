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

namespace Webkul\Marketplace\Block\Order\Invoice;

/**
 * Webkul Marketplace Order Invoice History Block.
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order\Invoice;
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
     * @var Invoice
     */
    protected $invoiceModel;

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
     * @param Invoice $invoiceModel
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
        Invoice $invoiceModel,
        Collection $saleslistCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_customerSession = $customerSession;
        $this->ordersHelper = $ordersHelper;
        $this->invoiceModel = $invoiceModel;
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
        $this->pageConfig->getTitle()->set(__('My Invoices'));
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
     * @return bool|\Magento\Sales\Model\Order\Invoice\Collection
     */

    public function getCollection()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $tracking = $this->ordersHelper->getOrderinfo($orderId);
        $invoices = [];
        if ($tracking) {
            $invoiceIds = [];
            $invoiceIds = explode(',', $tracking->getInvoiceId());
            $invoices = $this->invoiceModel->getCollection()
                          ->addFieldToFilter(
                              'entity_id',
                              ['in' => $invoiceIds]
                          );
        }
        return $invoices;
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
                'marketplace.order.invoice.pager'
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

    /**
     * Get Invoice Total Amount
     *
     * @param int $invoiceId
     * @param float $grandTotal
     * @return float $grandTotal
     */
    public function getInvoiceTotalAmount($invoiceId, $grandTotal)
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $source = $this->saleslistCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $orderId
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->getCustomerId()
        )->getSellerInvoiceTotals($invoiceId);
        if (isset($source[0])) {
            $source = $source[0];
            $grandTotal = $source['magepro_price']+
                $source['shipping_charges'] +
                $source['total_tax'] -
                $source['applied_coupon_amount'];
        }
        return $grandTotal;
    }
}
