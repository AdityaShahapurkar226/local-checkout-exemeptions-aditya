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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for preorder Complete search results.
 * @api
 */
interface AdminSellerResultsInterface extends SearchResultsInterface
{
    /**
     * Get sellerlist Complete list on search.
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface[]
     */
    public function getItems();

    /**
     * Set sellerlist Complete list on search.
     *
     * @param \Webkul\Marketplace\Api\Data\SellerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
