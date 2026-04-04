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

class Items extends \Webkul\Marketplace\Block\Order\Items
{
    /**
     * Set totals
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->itemCollectionFactory->create();
        $salesInvoiceItem = $this->itemCollection->getTable('sales_invoice_item');
        $marketplaceSaleslist = $this->itemCollection->getTable('marketplace_saleslist');
        $this->itemCollection->getSelect()->join(
            $salesInvoiceItem.' as invoice_item',
            'invoice_item.order_item_id = main_table.item_id'
        );
        $this->itemCollection->getSelect()->join(
            $marketplaceSaleslist.' as msl',
            'msl.order_item_id = main_table.item_id AND msl.order_id = main_table.order_id',
            [
                '*'
            ]
        )->where(
            'msl.seller_id = '.$this->getCustomerId().'
          AND main_table.order_id = '.$this->getOrder()->getId().'
          AND invoice_item.parent_id = '.$this->getInvoice()->getId()
        );
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
}
