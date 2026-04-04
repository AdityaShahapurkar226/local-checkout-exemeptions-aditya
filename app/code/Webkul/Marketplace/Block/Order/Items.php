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

use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Store\Model\ScopeInterface;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;

class Items extends \Webkul\Marketplace\Block\Order\View
{

    /**
     * Set collection to pager
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->itemCollectionFactory->create();
        $marketplaceSaleslist = $this->itemCollection->getTable('marketplace_saleslist');
        $this->itemCollection->getSelect()->join(
            $marketplaceSaleslist.' as msl',
            'msl.order_item_id = main_table.item_id AND msl.order_id = main_table.order_id',
            [
                'msl.seller_id AS seller_id',
                'msl.total_amount AS total_amount',
                'msl.actual_seller_amount AS actual_seller_amount',
                'msl.total_commission AS total_commission',
                'msl.magepro_price AS magepro_price',
                'msl.applied_coupon_amount AS applied_coupon_amount',
                'msl.total_tax AS total_tax'
            ]
        )->where('msl.seller_id = "'.$this->getCustomerId().'" AND main_table.order_id = '.$this->getOrder()->getId());
        $this->itemCollection = $this->addAdditionalFilters($this->itemCollection);
        $this->itemCollection->addFieldToFilter('main_table.parent_item_id', ['null' => true]);
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('marketplace_order_item_pager');
        if ($pagerBlock) {
            $pagerBlock->setLimit($this->itemsPerPage);
            //here pager updates collection parameters
            $pagerBlock->setCollection($this->itemCollection);
            $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
            $pagerBlock->setShowAmounts($this->isPagerDisplayed());
        }

        return parent::_prepareLayout();
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * Is pager displayed
     *
     * @return bool
     */
    public function isPagerDisplayed()
    {
        $pagerBlock = $this->getChildBlock('marketplace_order_item_pager');
        return $pagerBlock && ($this->itemCollection->getSize() > $this->itemsPerPage);
    }

    /**
     * Get pager Html
     *
     * @return string HTML output
     */
    public function getPagerHtml()
    {

        $pagerBlock = $this->getChildBlock('marketplace_order_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * Add additional filters
     *
     * @param object $itemCollection
     * @return void
     */
    public function addAdditionalFilters($itemCollection)
    {
        $itemCollection->getSelect()->columns('msl.currency_rate AS currency_rate');
        return $itemCollection;
    }

    /**
     * Get Order Invoice Totals
     *
     * @param string $invoiceIds
     * @return array $item
     */
    public function getOrderInvoiceTotals($invoiceIds)
    {
        $invoiceIds = explode(',', $invoiceIds);
        $invoices = $this->getOrderInvoiceCollection($invoiceIds);
        $invoiceGrandTotal = 0;
        $invoiceShippingAmount = 0;
        $transactionId = 0;
        foreach ($invoices as $invoice) {
            $invoiceGrandTotal = $invoiceGrandTotal + $invoice->getGrandTotal();
            $invoiceShippingAmount = $invoiceShippingAmount + $invoice->getShippingAmount();
            if ($invoice->getTransactionId()) {
                $transactionId = $invoice->getTransactionId();
            }
        }
        return [
            'grand_total' => $invoiceGrandTotal,
            'shipping_amount' => $invoiceShippingAmount,
            'transaction_id' => $transactionId
        ];
    }

    /**
     * Get Merged Items
     *
     * @param array $item
     * @return array $item
     */
    public function getMergedItems($item)
    {
        return array_merge([$item], $item->getChildrenItems());
    }

    /**
     * Get Item Option Data
     *
     * @param array $options
     * @param array $result
     * @return array $result
     */
    public function getItemOptionData($options, $result)
    {
        if (isset($options['options'])) {
            $result = array_merge($result, $options['options']);
        }
        if (isset($options['additional_options'])) {
            $result = array_merge($result, $options['additional_options']);
        }
        if (isset($options['attributes_info'])) {
            $result = array_merge($result, $options['attributes_info']);
        }
        return $result;
    }
}
