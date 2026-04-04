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
 * MpApi Feedback Interface
 * @api
 */
interface FeedbackInterface extends \Webkul\Marketplace\Api\Data\FeedbackInterface
{
    public const BUYER_ID = 'buyer_id';
    public const BUYER_EMAIL = 'buyer_email';
    public const FEED_PRICE     = 'feed_price';
    public const FEED_VALUE = 'feed_value';
    public const FEED_QUALITY = 'feed_quality';
    public const FEED_NICKNAME = 'feed_nickname';
    public const FEED_SUMMARY = 'feed_summary';
    public const FEED_REVIEW = 'feed_review';

    /**
     * Get Buyer ID
     *
     * @return int|null
     */
    public function getBuyerId();

    /**
     * Get Buyer Email
     *
     * @return string
     */
    public function getBuyerEmail();

    /**
     * Get Feed Price
     *
     * @return float
     */
    public function getFeedPrice();

    /**
     * Get Feed Price
     *
     * @return int
     */
    public function getFeedValue();

    /**
     * Get Feed Quality
     *
     * @return int
     */
    public function getFeedQuality();

    /**
     * Get Feed Nickname
     *
     * @return string
     */
    public function getFeedNickname();

    /**
     * Get Feed Summary
     *
     * @return string
     */
    public function getFeedSummary();

    /**
     * Get Feed Review
     *
     * @return string
     */
    public function getFeedReview();

    /**
     * Set Buyer ID
     *
     * @param int $buyerId
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setBuyerId($buyerId);

    /**
     * Set Buyer Email
     *
     * @param string $buyerEmail
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setBuyerEmail($buyerEmail);

    /**
     * Set Feed Price
     *
     * @param float $feedPrice
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedPrice($feedPrice);

    /**
     * Set Feed Price
     *
     * @param int $feedValue
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedValue($feedValue);

    /**
     * Set Feed Quality
     *
     * @param int $feedQuality
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedQuality($feedQuality);

    /**
     * Set Feed Nickname
     *
     * @param string $feedNickname
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedNickname($feedNickname);

    /**
     * Set Feed Summary
     *
     * @param string $feedSummary
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedSummary($feedSummary);

    /**
     * Set Feed Review
     *
     * @param string $feedReview
     * @return Webkul\MpApi\Api\Data\FeedbackInterface
     */
    public function setFeedReview($feedReview);
}
