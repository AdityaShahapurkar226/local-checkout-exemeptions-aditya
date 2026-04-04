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
namespace Webkul\Marketplace\Block\Order\Invoice\Create;

class Items extends \Webkul\Marketplace\Block\Order\Items
{

    /**
     * Set collection to pager
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $itemIds = $this->getInvoiceItems();

        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->itemCollectionFactory->create()
                                    ->addFieldToFilter('item_id', ['in'=>$itemIds]);
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
     * Retrieve order registered invoice instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve order registered invoice itemIds
     *
     * @return array $itemIds
     */
    public function getInvoiceItems()
    {
        $currentInvoiceItems = $this->getInvoice()->getAllItems();
        $itemIds = [];
        foreach ($currentInvoiceItems as $currentInvoiceItem) {
            array_push($itemIds, $currentInvoiceItem->getOrderItemId());
        }
        return $itemIds;
    }

    /**
     * Retrieve order registered invoice itemIds qty array
     *
     * @return array $qtyArr
     */
    public function getInvoiceItemsQty()
    {
        $currentInvoiceItems = $this->getInvoice()->getAllItems();
        $qtyArr = [];
        foreach ($currentInvoiceItems as $currentInvoiceItem) {
            $qtyArr[$currentInvoiceItem->getOrderItemId()] = $currentInvoiceItem->getQty();
        }
        return $qtyArr;
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
