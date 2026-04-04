<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Block\Order\Creditmemo;

class Totals extends \Webkul\Customattribute\Block\Order\Totals
{
   /**
    * Add FPT details
    */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $creditmemoId = $parent->getCreditmemo()->getId();
        $rewardAmountData = $this->orderCollection
        ->addFieldToFilter(
            'main_table.order_id',
            $this->getOrder()->getId()
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
        $salesCreditmemoItem = $this->orderCollection->getTable('sales_creditmemo_item');
        $rewardAmountData->getSelect()->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.order_item_id = main_table.order_item_id'
        )->where('creditmemo_item.parent_id = '.$creditmemoId);
        $source = $this->getSource();
        $order = $this->getOrder();
        $store = $order->getStore();
        $weeeTotal = $this->weeeData->getTotalAmounts($rewardAmountData, $store);
        $weeeBaseTotal = $this->weeeData->getBaseTotalAmounts($rewardAmountData, $store);
        if ($weeeTotal) {
            $totals = $this->getParentBlock()->getTotals();
            $currencyRate = $source[0]['currency_rate'];
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
            $this->_initOrderedTotal($currencyRate);
            $this->_initVendorTotal($currencyRate);
        }
        
        return $this;
    }
    /**
     * Init order totals
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
}
