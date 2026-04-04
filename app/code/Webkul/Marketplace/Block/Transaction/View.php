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

namespace Webkul\Marketplace\Block\Transaction;

use Webkul\Marketplace\Model\Sellertransaction;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistColl;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as OrdersColl;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Sellertransaction
     */
    protected $sellertransaction;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var SaleslistColl
     */
    public $saleslistCollection;

    /**
     * @var OrdersColl
     */
    public $ordersCollection;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param Sellertransaction $sellertransaction
     * @param HelperData $helper
     * @param SaleslistColl $saleslistCollection
     * @param OrdersColl $ordersCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        Sellertransaction $sellertransaction,
        HelperData $helper,
        SaleslistColl $saleslistCollection,
        OrdersColl $ordersCollection,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_order = $order;
        $this->sellertransaction = $sellertransaction;
        $this->helper = $helper;
        $this->saleslistCollection = $saleslistCollection;
        $this->ordersCollection = $ordersCollection;
        parent::__construct($context, $data);
    }

    /**
     * Get seller transaction details
     *
     * @param int $id
     * @return void
     */
    public function sellertransactionOrderDetails($id)
    {
        $sellerId = $this->helper->getCustomerId();
        return $this->saleslistCollection->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )->addFieldToFilter(
            'trans_id',
            $id
        )->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        );
    }

    /**
     * Get seller shiping amount
     *
     * @param int $sellerId
     * @param int $orderId
     * @return float
     */
    public function sellerOrderShippingAmount($sellerId, $orderId)
    {
        $coll = $this->ordersCollection->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )->addFieldToFilter(
            'order_id',
            $orderId
        );
        $shippingAmount = 0;
        foreach ($coll as $key => $value) {
            $shippingAmount = $value->getShippingCharges();
        }
        return $shippingAmount;
    }

    /**
     * Get formatted amount
     *
     * @param integer $price
     * @return string
     */
    public function getFormatedPrice($price = 0)
    {
        return $this->helper->getFormatedPrice($price);
    }

    /**
     * Get seller transaction
     *
     * @return void
     */
    public function sellertransactionDetails()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->sellertransaction->load($id);
    }
}
