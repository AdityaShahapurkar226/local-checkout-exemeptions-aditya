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

use Webkul\Marketplace\Model\Feedback as MpFeedback;
use Webkul\MpApi\Api\Data\FeedbackInterface;

/**
 * MpApi Feedback Model
 */
class Feedback extends MpFeedback implements FeedbackInterface
{
    /**
     * @inheritDoc
     */
    public function getBuyerId()
    {
        return parent::getData(self::BUYER_ID);
    }
    
    /**
     * @inheritDoc
     */
    public function getBuyerEmail()
    {
        return parent::getData(self::BUYER_EMAIL);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedPrice()
    {
        return parent::getData(self::FEED_PRICE);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedValue()
    {
        return parent::getData(self::FEED_VALUE);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedQuality()
    {
        return parent::getData(self::FEED_QUALITY);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedNickname()
    {
        return parent::getData(self::FEED_NICKNAME);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedSummary()
    {
        return parent::getData(self::FEED_SUMMARY);
    }
    
    /**
     * @inheritDoc
     */
    public function getFeedReview()
    {
        return parent::getData(self::FEED_REVIEW);
    }

    /**
     * @inheritDoc
     */
    public function setBuyerId($arg)
    {
        return $this->setData(self::BUYER_ID, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setBuyerEmail($arg)
    {
        return $this->setData(self::BUYER_EMAIL, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedPrice($arg)
    {
        return $this->setData(self::FEED_PRICE, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedValue($arg)
    {
        return $this->setData(self::FEED_VALUE, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedQuality($arg)
    {
        return $this->setData(self::FEED_QUALITY, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedNickname($arg)
    {
        return $this->setData(self::FEED_NICKNAME, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedSummary($arg)
    {
        return $this->setData(self::FEED_SUMMARY, $arg);
    }

    /**
     * @inheritDoc
     */
    public function setFeedReview($arg)
    {
        return $this->setData(self::FEED_REVIEW, $arg);
    }
}
