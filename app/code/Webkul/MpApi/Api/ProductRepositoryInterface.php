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
 * ProductRepository CRUD Interface
 */
interface ProductRepositoryInterface
{
    /**
     * Get by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save Record.
     *
     * @param \Webkul\Marketplace\Api\Data\ProductInterface $subject
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     */
    public function save(\Webkul\Marketplace\Api\Data\ProductInterface $subject);

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete Record.
     *
     * @param \Webkul\Marketplace\Api\Data\ProductInterface $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\ProductInterface $subject);

    /**
     * Delete recod by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
