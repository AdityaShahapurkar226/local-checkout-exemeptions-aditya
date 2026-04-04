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

namespace Webkul\Marketplace\Block\Order;

/*
 * Webkul Marketplace Order History Block
 */
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\OrdersFactory as MpOrderModel;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    public $customer;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $order;

    /**
     * @var Session
     */
    public $_customerSession;

    /**
     * @var CollectionFactory
     */
    public $_orderCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    public $salesOrderLists;

    /** @var \Magento\Sales\Model\OrderRepository */
    public $orderRepository;

    /** @var \Magento\Catalog\Model\ProductRepository */
    public $productRepository;

    /** @var SaleslistFactory */
    public $saleslistModel;

    /** @var Webkul\Marketplace\Helper\Orders */
    public $ordersHelper;
    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OrderFactory                                     $order
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param CollectionFactory                                $orderCollectionFactory
     * @param \Magento\Sales\Model\OrderRepository             $orderRepository
     * @param \Magento\Catalog\Model\ProductRepository         $productRepository
     * @param SaleslistFactory                                 $saleslistModel
     * @param \Webkul\Marketplace\Helper\Orders                $ordersHelper
     * @param MpHelper                                         $mpHelper
     * @param MpOrderModel                                     $mpOrderModel
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        OrderFactory $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        SaleslistFactory $saleslistModel,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        MpHelper $mpHelper,
        MpOrderModel $mpOrderModel,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->saleslistModel = $saleslistModel;
        $this->ordersHelper = $ordersHelper;
        $this->mpHelper = $mpHelper;
        $this->mpOrderModel = $mpOrderModel;
        
        parent::__construct($context, $data);
    }

    /**
     * Set title
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
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
     * Get all sales order
     *
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    public function getAllSalesOrder()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        try {
            if (!$this->salesOrderLists) {
                $paramData = $this->getRequest()->getParams();
                $filterOrderid = '';
                $filterOrderstatus = '';
                $filterDataTo = '';
                $filterDataFrom = '';
                $from = null;
                $to = null;

                if (isset($paramData['s'])) {
                    $filterOrderid = $paramData['s'] != '' ? $paramData['s'] : '';
                }
                if (isset($paramData['orderstatus'])) {
                    $filterOrderstatus = $paramData['orderstatus'] != '' ? $paramData['orderstatus'] : '';
                }
                if (isset($paramData['from_date'])) {
                    $filterDataFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
                }
                if (isset($paramData['to_date'])) {
                    $filterDataTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
                }

                $orderids = $this->getOrderIdsArray($customerId, $filterOrderstatus);

                $ids = $this->getEntityIdsArray($orderids);

                $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(
                    '*'
                )
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $ids]
                );

                if ($filterDataTo) {
                    $todate = date_create($filterDataTo);
                    $to = date_format($todate, 'Y-m-d 23:59:59');
                }
                if ($filterDataFrom) {
                    $fromdate = date_create($filterDataFrom);
                    $from = date_format($fromdate, 'Y-m-d H:i:s');
                }

                if ($filterOrderid) {
                    $collection->addFieldToFilter(
                        'magerealorder_id',
                        ['eq' => $filterOrderid]
                    );
                }
                if ($from && $to) {
                    $collection->addFieldToFilter(
                        'created_at',
                        ['datetime' => true, 'from' => $from, 'to' => $to]
                    );
                }

                $collection->setOrder(
                    'created_at',
                    'desc'
                );
                $collection->getSellerOrderCollection();
                $this->salesOrderLists = $collection;
            }
        } catch (\Exception $e) {
            $this->mpHelper->logDataInLogger("Block_Order_History getAllSalesOrder : ".$e->getMessage());
        }
        return $this->salesOrderLists;
    }

    /**
     * Get order id array
     *
     * @param string $customerId
     * @param string $filterOrderstatus
     * @return array
     */
    public function getOrderIdsArray($customerId = '', $filterOrderstatus = '')
    {
        $orderids = [];

        $collectionOrders = $this->saleslistModel->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $customerId]
                            )
                            ->addFieldToSelect('order_id')
                            ->distinct(true);
        if ($buyerId = $this->getRequest()->getParam('customer_id')) {
            $buyerIds = $collectionOrders->getAllBuyerIds();
            if (in_array($buyerId, $buyerIds)) {
                $collectionOrders->addFieldToFilter('magebuyer_id', $buyerId);
            }
        }

        foreach ($collectionOrders as $collectionOrder) {
            $tracking = $this->ordersHelper->getOrderinfo($collectionOrder->getOrderId());

            if ($tracking) {
                if ($filterOrderstatus) {
                    if ($tracking->getIsCanceled()) {
                        if ($filterOrderstatus == 'canceled') {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    } else {
                        $tracking = $this->orderRepository->get($collectionOrder->getOrderId());
                        if ($tracking->getStatus() == $filterOrderstatus) {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    }
                } else {
                    array_push($orderids, $collectionOrder->getOrderId());
                }
            }
        }

        return $orderids;
    }

    /**
     * Get entity id array
     *
     * @param array $orderids
     * @return array
     */
    public function getEntityIdsArray($orderids = [])
    {
        $ids = [];
        foreach ($orderids as $orderid) {
            $collectionIds = $this->saleslistModel->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                ['eq' => $orderid]
                            )
                            ->addFieldToFilter('parent_item_id', ['null' => 'true'])
                            ->setOrder('entity_id', 'DESC')
                            ->setPageSize(1);
            foreach ($collectionIds as $collectionId) {
                $autoid = $collectionId->getId();
                array_push($ids, $autoid);
            }
        }

        return $ids;
    }

    /**
     * Set collection
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllSalesOrder()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.dashboard.pager'
            )
            ->setCollection(
                $this->getAllSalesOrder()
            );
            $this->setChild('pager', $pager);
            $this->getAllSalesOrder()->load();
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
     * @return void
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * Get product name
     *
     * @param [int] $orderId
     * @param boolean $wrap
     * @return string
     */
    public function getpronamebyorder($orderId, $wrap = false)
    {
        $orderHelper = $this->ordersHelper;
        $sellerId = $this->getCustomerId();
        $collection = $this->_orderCollectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        )
        ->addFieldToFilter('parent_item_id', ['null' => 'true']);
        $productName = '';
        $result = "";
        foreach ($collection as $res) {
            if ($res->getParentItemId()) {
                continue;
            }

            $productName = $orderHelper->getOrderedProductName($res, '', $wrap);
            if ($wrap) {
                $result .= "<div class='wk-order-item-block'>".$productName."</div>";
            } else {
                $result .= $productName;
            }
        }

        return $result;
    }

    /**
     * Get price by order
     *
     * @param [int] $orderId
     * @return float
     */
    public function getPricebyorder($orderId)
    {
        $sellerId = $this->getCustomerId();
        $collection = $this->saleslistModel->create()
                      ->getCollection()
                      ->addFieldToFilter(
                          'main_table.seller_id',
                          $sellerId
                      )->addFieldToFilter(
                          'main_table.order_id',
                          $orderId
                      )->getPricebyorderData();
        $name = '';
        $actualSellerAmount = 0;
        foreach ($collection as $coll) {
            // calculate order actual_seller_amount in base currency
            $appliedCouponAmount = $coll['applied_coupon_amount']*1;
            $shippingAmount = $coll['shipping_charges']*1;
            $refundedShippingAmount = $coll['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            if ($coll['tax_to_seller']) {
                $vendorTaxAmount = $coll['total_tax']*1;
            } else {
                $vendorTaxAmount = 0;
            }
            if ($coll['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping;
                $actualSellerAmount += $coll['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping;
                }
            }
        }
        return $actualSellerAmount;
    }

    /**
     * Get ordered price
     *
     * @param [float] $currencyRate
     * @param [float] $basePrice
     * @return float
     */
    public function getOrderedPricebyorder($currencyRate, $basePrice)
    {
        return $basePrice * $currencyRate;
    }
/**
 * Get main order
 *
 * @param [int] $orderId
 * @return array
 */
    public function getMainOrder($orderId)
    {
        $sellerId = $this->getCustomerId();
        $collection = $this->Order->create()
                      ->getCollection()
                      ->addFieldToFilter(
                          'entity_id',
                          ['eq' => $orderId]
                      );
        foreach ($collection as $res) {
            return $res;
        }

        return [];
    }

    /**
     * Get seller order info
     *
     * @param string $orderId
     * @return array
     */
    public function getSellerOrderInfo($orderId = '')
    {
        $collection = $this->mpOrderModel->create()->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        );

        return $collection->getData()[0];
    }
}
