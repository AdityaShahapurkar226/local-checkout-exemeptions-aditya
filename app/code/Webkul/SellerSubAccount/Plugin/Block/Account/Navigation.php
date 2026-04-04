<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Plugin\Block\Account;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityAttribute;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection;
use Webkul\Marketplace\Model\Product as MarketplaceProduct;
use Webkul\Marketplace\Model\Orders as MarketplaceOrders;
use Webkul\Marketplace\Model\Sellertransaction;
use Webkul\Marketplace\Model\Saleslist;

class Navigation
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var EntityAttribute
     */
    public $_entityAttribute;

    /**
     * @var Collection
     */
    public $_collection;

    /**
     * @var MarketplaceProduct
     */
    public $_marketplaceProduct;

    /**
     * @var MarketplaceOrders
     */
    public $_marketplaceOrders;

    /**
     * @var Sellertransaction
     */
    public $_sellertransaction;

    /**
     * @var Saleslist
     */
    public $_saleslist;

    /**
     * @param HelperData          $helper
     * @param MarketplaceHelper   $marketplaceHelper
     * @param EntityAttribute     $entityAttribute
     * @param Collection          $collection
     * @param MarketplaceProduct  $marketplaceProduct
     * @param MarketplaceOrders   $marketplaceOrders
     * @param Sellertransaction   $sellertransaction
     * @param Saleslist           $saleslist
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        EntityAttribute $entityAttribute,
        Collection $collection,
        MarketplaceProduct $marketplaceProduct,
        MarketplaceOrders $marketplaceOrders,
        Sellertransaction $sellertransaction,
        Saleslist $saleslist
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_entityAttribute = $entityAttribute;
        $this->_collection = $collection;
        $this->_marketplaceProduct = $marketplaceProduct;
        $this->_marketplaceOrders = $marketplaceOrders;
        $this->_sellertransaction = $sellertransaction;
        $this->_saleslist = $saleslist;
    }

    /**
     * GetProductCollection.
     *
     * @param \Webkul\Marketplace\Block\Account\Navigation $block
     * @param \Closure $proceed
     *
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    public function aroundGetProductCollection(
        \Webkul\Marketplace\Block\Account\Navigation $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        if (!($customerId = $this->_helper->getSubAccountSellerId())) {
            return false;
        }
        $storeCollection = $this->_marketplaceProduct
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        );
        return $storeCollection;
    }

    /**
     * GetMarketplaceOrderCollection.
     *
     * @param \Webkul\Marketplace\Block\Account\Navigation $block
     * @param \Closure $proceed
     *
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Order\Collection
     */
    public function aroundGetMarketplaceOrderCollection(
        \Webkul\Marketplace\Block\Account\Navigation $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        if (!($customerId = $this->_helper->getSubAccountSellerId())) {
            return false;
        }
        $orderCollection = $this->_marketplaceOrders
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        );
        return $orderCollection;
    }

    /**
     * GetTransactionCollection.
     *
     * @param \Webkul\Marketplace\Block\Account\Navigation $block
     * @param \Closure $proceed
     *
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Transaction\Collection
     */
    public function aroundGetTransactionCollection(
        \Webkul\Marketplace\Block\Account\Navigation $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        if (!($customerId = $this->_helper->getSubAccountSellerId())) {
            return false;
        }
        $transactionCollection = $this->_sellertransaction
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $customerId
        )->addFieldToFilter(
            'seller_pending_notification',
            1
        )->setOrder('created_at', 'DESC');
        return $transactionCollection;
    }

    /**
     * GetOrderNotificationDesc.
     *
     * @param \Webkul\Marketplace\Block\Account\Navigation $block
     * @param \Closure $proceed
     * @param int $orderId
     *
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Transaction\Collection
     */
    public function aroundGetOrderNotificationDesc(
        \Webkul\Marketplace\Block\Account\Navigation $block,
        \Closure $proceed,
        $orderId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed($orderId);
            return $result;
        }
        if (!($customerId = $this->_helper->getSubAccountSellerId())) {
            return false;
        }
        $order = $block->loadOrder($orderId);
        $saleslistIds = [];
        $collection1 = $this->_saleslist->getCollection()
        ->addFieldToFilter('order_id', $orderId)
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('parent_item_id', ['null' => 'true'])
        ->addFieldToFilter('magerealorder_id', ['neq' => 0])
        ->addFieldToSelect('entity_id');

        $saleslistIds = $collection1->getData();

        $fetchsale = $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $saleslistIds]
        );
        $fetchsale->getSellerOrderCollection();
        $productNames = [];
        foreach ($fetchsale as $value) {
            $productNames[] = $value->getMageproName();
        }
        $productNames = implode(',', $productNames);
        return __(
            sprintf(
                'Product(s) %s has been sold from your store with order id %s',
                '<span class="wk-focus">'.$productNames.'</span>',
                '<span class="wk-focus">#'.$order->getIncrementId().'</span>'
            )
        );
    }
}
