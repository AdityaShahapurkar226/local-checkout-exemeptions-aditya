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
namespace Webkul\MarketplaceBaseShipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * shipping setting info CRUD interface.
 * @api
 */
interface ShippingSettingRepositoryInterface
{
    /**
     * Save customer data.
     *
     * @param \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface $items
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\ShippingSettingInterface $items);

    /**
     * Retrieve customer data.
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\ShippingSettingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve customer data.
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\ShippingSettingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySellerId($sellerId);

    /**
     * Delete customer data.
     *
     * @param \Magento\Cms\Api\Data\PreorderItemsInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ShippingSettingInterface $item);

    /**
     * Delete customer.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
