<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ShippingSettingSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface[]
     */
    public function getItems();

    /**
     * @param \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
