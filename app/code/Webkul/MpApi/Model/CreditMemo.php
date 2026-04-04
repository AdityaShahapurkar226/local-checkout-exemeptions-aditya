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
namespace Webkul\MpApi\Model;

use \Webkul\MpApi\Api\Data\CreditMemoInterface;

class CreditMemo extends \Magento\Framework\DataObject implements CreditMemoInterface
{
    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->getData(CreditMemoInterface::ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function getShippingAmount()
    {
        return $this->getData(CreditMemoInterface::SHIPPING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getAdjustmentNegative()
    {
        return $this->getData(CreditMemoInterface::ADJUSTMENT_NEGATIVE);
    }

    /**
     * @inheritDoc
     */
    public function getAdjustmentPositive()
    {
        return $this->getData(CreditMemoInterface::ADJUSTMENT_POSITIVE);
    }

    /**
     * @inheritDoc
     */
    public function getDoOffline()
    {
        return $this->getData(CreditMemoInterface::DO_OFFLINE);
    }

    /**
     * @inheritDoc
     */
    public function getCommentText()
    {
        return $this->getData(CreditMemoInterface::COMMENT_TEXT);
    }

    /**
     * @inheritDoc
     */
    public function getCommentCustomerNotify()
    {
        return $this->getData(CreditMemoInterface::COMMENT_CUSTOMER_NOTIFY);
    }

    /**
     * @inheritDoc
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(CreditMemoInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * @inheritDoc
     */
    public function getSendEmail()
    {
        return $this->getData(CreditMemoInterface::SEND_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setItems($items)
    {
        return $this->setData(CreditMemoInterface::ITEMS, $items);
    }

    /**
     * @inheritDoc
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(CreditMemoInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function setAdjustmentPositive($adjustmentPositive)
    {
        return $this->setData(CreditMemoInterface::ADJUSTMENT_POSITIVE, $adjustmentPositive);
    }

    /**
     * @inheritDoc
     */
    public function setAdjustmentNegative($adjustmentPositive)
    {
        return $this->setData(CreditMemoInterface::ADJUSTMENT_NEGATIVE, $adjustmentPositive);
    }

    /**
     * @inheritDoc
     */
    public function setDoOffline($doOffline)
    {
        return $this->setData(CreditMemoInterface::DO_OFFLINE, $doOffline);
    }

    /**
     * @inheritDoc
     */
    public function setCommentText($comment)
    {
        return $this->setData(CreditMemoInterface::COMMENT_TEXT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function setCommentCustomerNotify($flag)
    {
        return $this->setData(CreditMemoInterface::COMMENT_CUSTOMER_NOTIFY, $flag);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisibleOnFront($flag)
    {
        return $this->setData(CreditMemoInterface::IS_VISIBLE_ON_FRONT, $flag);
    }

    /**
     * @inheritDoc
     */
    public function setSendEmail($flag)
    {
        return $this->setData(CreditMemoInterface::SEND_EMAIL, $flag);
    }
}
