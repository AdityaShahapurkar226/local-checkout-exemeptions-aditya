<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
 
namespace Webkul\Marketplace\Api\Data;

interface FeedbackSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Webkul\Marketplace\Api\Data\FeedbackInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Webkul\Marketplace\Api\Data\FeedbackInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
