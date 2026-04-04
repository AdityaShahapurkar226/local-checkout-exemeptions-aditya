<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Block\Order;

use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * Associated array of seller order totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;

    /**
     * @param \Webkul\Marketplace\Helper\Data                   $helper
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param Collection                                        $orderCollection
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Weee\Helper\Data                         $weeeData
     * @param array                                             $data
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        Collection $orderCollection,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Weee\Helper\Data $weeeData,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->weeeData = $weeeData;
        $this->orderCollection = $orderCollection;
        parent::__construct(
            $context,
            $coreRegistry,
            $data
        );
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        $collection = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        )->getSellerOrderTotals();
        return $collection;
    }

    /**
     * Add FPT details
     *
     * @return void
     */
    public function initTotals()
    {
        $source = $this->getSource();
        $order = $this->getOrder();
        $items = $order->getAllItems();
        $store = $order->getStore();
        $orderAmountData = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
        $salesCreditmemoItem = $this->orderCollection->getTable('sales_order_item');
        $orderAmountData->getSelect()->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.item_id = main_table.order_item_id'
        )->where('creditmemo_item.order_id = '.$this->getOrder()->getId());
        $weeeTotal = $this->weeeData->getTotalAmounts($orderAmountData, $store);
        $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($orderAmountData, $store);
        if ($weeeTotal) {
            $totals = $this->getParentBlock()->getTotals();
            $currencyRate = $source[0]['currency_rate'];
            $totalOrdered = $this->getParentBlock()->getOrderedAmount($source[0])+$weeeTotal;
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'fpt_amount',
                    'label' => __('FPT'),
                    'value' => $weeeTotal,
                    'base_value' => $weeeBaseTotal
                ]
            );
        
            if (isset($totals['grand_total_incl'])) {
                $this->getParentBlock()->addTotalBefore($total, 'grand_total');
            } else {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            }
            $this->_initOrderedTotal();
            $this->_initVendorTotal();
            $this->_initBaseOrderedTotal();
            $this->_initBaseVendorTotal();
        }
        
        return $this;
    }
    /**
     * Calculate order total with FPT
     *
     * @return void
     */
    protected function _initOrderedTotal()
    {
        $parent = $this->getParentBlock();
        $fptamount = $parent->getTotal('fpt_amount');
        $total = $parent->getTotal('ordered_total');
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $fptamount->getValue());
        return $this;
    }

    /**
     * Calculate vendor and admin commission with FPT
     *
     * @return void
     */
    protected function _initVendorTotal()
    {
        $parent = $this->getParentBlock();
        $fptamount = $parent->getTotal('fpt_amount');
        $source = $this->getSource();
        if (isset($source[0])) {
            $source = $source[0];
            if ($source['tax_to_seller'] ==1) {
                $total = $parent->getTotal('vendor_total');
                if (!$total) {
                    return $this;
                }
                $total->setValue($total->getValue() + $fptamount->getValue());
            } else {
                $total = $parent->getTotal('admin_commission');
                if (!$total) {
                    return $this;
                }
                $total->setValue($total->getValue() + $fptamount->getValue());
            }
        }
        
        return $this;
    }

    /**
     * Calculate base vendor total
     *
     * @return void
     */
    protected function _initBaseVendorTotal()
    {
        $parent = $this->getParentBlock();
        $fptamount = $parent->getTotal('fpt_amount');
        $source = $this->getSource();
        if (isset($source[0])) {
            $source = $source[0];
            if ($source['tax_to_seller'] ==1) {
                $total = $parent->getTotal('base_vendor_total');
                if (!$total) {
                    return $this;
                }
                $total->setValue($total->getValue() + $fptamount->getBaseValue());
            } else {
                $total = $parent->getTotal('base_admin_commission');
                if (!$total) {
                    return $this;
                }
                $total->setValue($total->getValue() + $fptamount->getBaseValue());
            }
        }
        
        return $this;
    }

    /**
     * Calculate base total with FPT
     *
     * @return void
     */
    protected function _initBaseOrderedTotal()
    {
       
        $parent = $this->getParentBlock();
        $reward = $parent->getTotal('fpt_amount');
        $total = $parent->getTotal('base_ordered_total');
       
        if (!$total) {
            return $this;
        }
        $total->setValue($total->getValue() + $reward->getBaseValue());
        return $this;
    }
}
