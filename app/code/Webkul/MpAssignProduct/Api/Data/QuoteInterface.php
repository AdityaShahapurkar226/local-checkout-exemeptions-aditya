<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Api\Data;

/**
 * Assigned Quote Interface
 */
interface QuoteInterface
{

    public const ID = 'id';

    public const ITEM_ID = 'item_id';

    public const OWNER_ID = 'owner_id';

    public const SELLER_ID = 'seller_id';

    public const QTY = 'qty';

    public const PRODUCT_ID = 'product_id';

    public const QUOTE_ID = 'quote_id';

    public const ASSIGN_ID = 'assign_id';

    public const CHILD_ASSIGN_ID = 'child_assign_id';

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setId($id);
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();
    /**
     * Set ItemId
     *
     * @param int $itemId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setItemId($itemId);
    /**
     * Get ItemId
     *
     * @return int
     */
    public function getItemId();
    /**
     * Set OwnerId
     *
     * @param int $ownerId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setOwnerId($ownerId);
    /**
     * Get OwnerId
     *
     * @return int
     */
    public function getOwnerId();
    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setSellerId($sellerId);
    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId();
    /**
     * Set Qty
     *
     * @param int $qty
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setQty($qty);
    /**
     * Get Qty
     *
     * @return int
     */
    public function getQty();
    /**
     * Set ProductId
     *
     * @param int $productId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setProductId($productId);
    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId();
    /**
     * Set QuoteId
     *
     * @param int $quoteId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setQuoteId($quoteId);
    /**
     * Get QuoteId
     *
     * @return int
     */
    public function getQuoteId();
    /**
     * Set AssignId
     *
     * @param int $assignId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setAssignId($assignId);
    /**
     * Get AssignId
     *
     * @return int
     */
    public function getAssignId();
    /**
     * Set ChildAssignId
     *
     * @param int $childAssignId
     * @return Webkul\MpAssignProduct\Api\Data\QuoteInterface
     */
    public function setChildAssignId($childAssignId);
    /**
     * Get ChildAssignId
     *
     * @return int
     */
    public function getChildAssignId();
}
