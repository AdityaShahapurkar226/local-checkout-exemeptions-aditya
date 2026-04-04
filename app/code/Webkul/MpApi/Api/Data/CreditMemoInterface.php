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
interface CreditMemoInterface
{
    public const ITEMS = 'items';
    public const SHIPPING_AMOUNT = 'shipping_amount';
    public const ADJUSTMENT_POSITIVE = 'adjustment_positive';
    public const ADJUSTMENT_NEGATIVE = 'adjustment_negative';
    public const DO_OFFLINE = 'do_offline';
    public const COMMENT_TEXT = 'comment_text';
    public const COMMENT_CUSTOMER_NOTIFY = 'comment_customer_notify';
    public const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    public const SEND_EMAIL = 'send_email';

    /**
     * Gets credit memo items.
     *
     * @return Webkul\MpApi\Api\Data\CreditMemoItemInterface[] Array of credit memo items.
     */
    public function getItems();

    /**
     * Gets the credit memo shipping amount.
     *
     * @return float|null Credit memo shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the credit memo negative adjustment.
     *
     * @return float|null Credit memo negative adjustment.
     */
    public function getAdjustmentNegative();

    /**
     * Gets the credit memo positive adjustment.
     *
     * @return float|null Credit memo positive adjustment.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the credit memo do offline.
     *
     * @return boolean
     */
    public function getDoOffline();

    /**
     * Gets the credit memo comment text
     *
     * @return string
     */
    public function getCommentText();

    /**
     * Gets the credit memo comment customer notify.
     *
     * @return boolean
     */
    public function getCommentCustomerNotify();

    /**
     * Gets the credit memo is visible on front.
     *
     * @return boolean
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the credit memo send email.
     *
     * @return boolean
     */
    public function getSendEmail();

    /**
     * Sets credit memo items.
     *
     * @param array $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Sets the credit memo shipping amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Sets the credit memo positive adjustment.
     *
     * @param float $adjustmentPositive
     * @return $this
     */
    public function setAdjustmentPositive($adjustmentPositive);

    /**
     * Sets the credit memo negative adjustment.
     *
     * @param float $adjustmentNegative
     * @return $this
     */
    public function setAdjustmentNegative($adjustmentNegative);

    /**
     * Sets the credit memo do offline.
     *
     * @param boolean $doOffline
     * @return $this
     */
    public function setDoOffline($doOffline);

    /**
     * Sets the credit memo comment text
     *
     * @param boolean $comment
     * @return string
     */
    public function setCommentText($comment);

    /**
     * Sets the credit memo comment customer notify.
     *
     * @param boolean $flag
     * @return boolean
     */
    public function setCommentCustomerNotify($flag);

    /**
     * Sets the credit memo is visible on front.
     *
     * @param boolean $flag
     * @return boolean
     */
    public function setIsVisibleOnFront($flag);

    /**
     * Sets the credit memo send email.
     *
     * @param boolean $flag
     * @return boolean
     */
    public function setSendEmail($flag);
}
