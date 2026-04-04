<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpApi
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpApi\Api;

/**
 * OrdersRepository CRUD Interface
 */
interface OrdersRepositoryInterface extends \Webkul\Marketplace\Api\OrdersRepositoryInterface
{
    /**
     * Retrieve orders by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save Orders.
     *
     * @param \Webkul\Marketplace\Api\Data\OrdersInterface $subject
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function save(\Webkul\Marketplace\Api\Data\OrdersInterface $subject);

    /**
     * Retrieve all orders.
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getLists(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete orders.
     *
     * @param \Webkul\Marketplace\Api\Data\OrdersInterface $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\OrdersInterface $subject);

    /**
     * Delete orders by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
