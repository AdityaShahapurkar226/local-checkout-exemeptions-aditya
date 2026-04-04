<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Helper;

class Data
{
    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $assignedItem;

    /**
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem
     */
    public function __construct(
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem
    ) {
        $this->assignedItem = $assignedItem;
    }

    /**
     * Plugin for getSellerProductDataByProductId
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @param $productId
     * @return $result
     */
    public function aroundGetSellerProductDataByProductId(
        \Webkul\Marketplace\Helper\Data $subject,
        \Closure $proceed,
        $productId
    ) {
        $collecton = $proceed($productId);
        if ($collecton->getSize()) {
            return $collecton;
        }
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('assign_product_id', $productId);
        return $assignItem;
    }

    /**
     * Plugin for getSellerIdByProductId
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @param $productId
     * @return $result
     */
    public function aroundGetSellerIdByProductId(
        \Webkul\Marketplace\Helper\Data $subject,
        \Closure $proceed,
        $productId
    ) {
        $sellerId = $proceed($productId);
        if ($sellerId) {
            return $sellerId;
        }
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('assign_product_id', $productId);
        return $assignItem->getFirstItem()->getSellerId();
    }
}
