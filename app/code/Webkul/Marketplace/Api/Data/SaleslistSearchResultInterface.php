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

interface SaleslistSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
