<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Api\Data;

/**
 * MpApi Device Token interface.
 *
 * @api
 */
interface CreditMemoItemInterface
{
    public const QTY = 'qty';
    public const BACK_TO_STOCK = 'back_to_stock';
    public const ITEM_ID = 'item_id';

    /**
     * Gets credit memo items qty.
     *
     * @return int Item qty.
     */
    public function getQty();

    /**
     * Sets credit memo item.
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Gets credit memo items Back To Stock.
     *
     * @return boolean Item Back To Stock.
     */
    public function getBackToStock();

    /**
     * Sets credit memo item back To Stock.
     *
     * @param string $backToStock
     * @return $this
     */
    public function setBackToStock($backToStock);

    /**
     * Gets credit memo item id.
     *
     * @return int Item Id.
     */
    public function getItemId();

    /**
     * Sets credit memo item id.
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);
}
