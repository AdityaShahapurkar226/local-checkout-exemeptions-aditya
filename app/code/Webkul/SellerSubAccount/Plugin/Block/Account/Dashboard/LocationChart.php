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
namespace Webkul\SellerSubAccount\Plugin\Block\Account\Dashboard;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\Saleslist;

class LocationChart
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
     * @var Order
     */
    public $_order;

    /**
     * @var Saleslist
     */
    public $_saleslist;

    /**
     * @param HelperData          $helper
     * @param MarketplaceHelper   $marketplaceHelper
     * @param Order               $order
     * @param Saleslist           $saleslist
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        Order $order,
        Saleslist $saleslist
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_order = $order;
        $this->_saleslist = $saleslist;
    }

    /**
     * GetYearlySaleLocation.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block
     * @param \Closure $proceed
     *
     * @return array
     */
    public function aroundGetYearlySaleLocation(
        \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        $data = [];
        if (!($sellerId = $this->_helper->getSubAccountSellerId())) {
            return $data;
        }
        $data = [];
        $curryear = date('Y');
        $_date1 = $curryear.'-01-01 00:00:00';
        $_date2 = $curryear.'-12-31 23:59:59';
        $_SellerOrderCollection = $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        );
        $orderSaleArr = [];
        foreach ($_SellerOrderCollection as $record) {
            if (!isset($orderSaleArr[$record->getOrderId()])) {
                $orderSaleArr[$record->getOrderId()] = $record->getActualSellerAmount();
            } else {
                $orderSaleArr[$record->getOrderId()] =
                $orderSaleArr[$record->getOrderId()] + $record->getActualSellerAmount();
            }
        }
        $orderIds = $_SellerOrderCollection->getAllOrderIds();
        $collection = $this->_order
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $_date1, 'to' => $_date2]
        );
        $data = $block->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    /**
     * GetMonthlySaleLocation.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block
     * @param \Closure $proceed
     *
     * @return array
     */
    public function aroundGetMonthlySaleLocation(
        \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        $data = [];
        if (!($sellerId = $this->_helper->getSubAccountSellerId())) {
            return $data;
        }
        $data = [];
        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $d1 = $curryear.'-'.$currMonth.'-01 00:00:00';
        $d2 = $curryear.'-'.$currMonth.'-'.$currDay.' 23:59:59';
        $_sellerOrderCollection = $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        );
        $orderSaleArr = [];
        foreach ($_sellerOrderCollection as $record) {
            if (!isset($orderSaleArr[$record->getOrderId()])) {
                $orderSaleArr[$record->getOrderId()] = $record->getActualSellerAmount();
            } else {
                $orderSaleArr[$record->getOrderId()] =
                $orderSaleArr[$record->getOrderId()] + $record->getActualSellerAmount();
            }
        }
        $orderIds = $_sellerOrderCollection->getAllOrderIds();
        $_collection = $this->_order
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $d1, 'to' => $d2]
        );
        $data = $block->getArrayData($_collection, $orderSaleArr);

        return $data;
    }

    /**
     * GetWeeklySaleLocation.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block
     * @param \Closure $proceed
     *
     * @return array
     */
    public function aroundGetWeeklySaleLocation(
        \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        $data = [];
        if (!($sellerId = $this->_helper->getSubAccountSellerId())) {
            return $data;
        }
        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $currWeekDay = date('N');
        $currWeekStartDay = $currDay - $currWeekDay;
        $currWeekEndDay = $currWeekStartDay + 7;
        $currentDayOfMonth = date('j');
        if ($currWeekEndDay > $currentDayOfMonth) {
            $currWeekEndDay = $currentDayOfMonth;
        }
        $date1 = $curryear.'-'.$currMonth.'-'.$currWeekStartDay.' 00:00:00';
        $date2 = $curryear.'-'.$currMonth.'-'.$currWeekEndDay.' 23:59:59';
        $sellerOrderCollection = $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        );
        $orderSaleArr = [];
        foreach ($sellerOrderCollection as $record) {
            if (!isset($orderSaleArr[$record->getOrderId()])) {
                $orderSaleArr[$record->getOrderId()] = $record->getActualSellerAmount();
            } else {
                $orderSaleArr[$record->getOrderId()] =
                $orderSaleArr[$record->getOrderId()] + $record->getActualSellerAmount();
            }
        }
        $orderIds = $sellerOrderCollection->getAllOrderIds();
        $collection = $this->_order
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $date1, 'to' => $date2]
        );
        $data = $block->getArrayData($collection, $orderSaleArr);

        return $data;
    }

    /**
     * GetDailySaleLocation.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block
     * @param \Closure $proceed
     *
     * @return array
     */
    public function aroundGetDailySaleLocation(
        \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        $data = [];
        if (!($sellerId = $this->_helper->getSubAccountSellerId())) {
            return $data;
        }

        $curryear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $dt1 = $curryear.'-'.$currMonth.'-'.$currDay.' 00:00:00';
        $dt2 = $curryear.'-'.$currMonth.'-'.$currDay.' 23:59:59';
        $sellerOrder_Collection = $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        )
        ->addFieldToFilter(
            'paid_status',
            ['neq' => 2]
        );
        $orderSaleArr = [];
        foreach ($sellerOrder_Collection as $record) {
            if (!isset($orderSaleArr[$record->getOrderId()])) {
                $orderSaleArr[$record->getOrderId()] = $record->getActualSellerAmount();
            } else {
                $orderSaleArr[$record->getOrderId()] =
                $orderSaleArr[$record->getOrderId()] + $record->getActualSellerAmount();
            }
        }
        $orderIds = $sellerOrder_Collection->getAllOrderIds();
        $Collection = $this->_order
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $orderIds]
        )
        ->addFieldToFilter(
            'created_at',
            ['datetime' => true, 'from' => $dt1, 'to' => $dt2]
        );
        $data = $block->getArrayData($Collection, $orderSaleArr);

        return $data;
    }
}
