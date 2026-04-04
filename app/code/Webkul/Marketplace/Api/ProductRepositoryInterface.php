<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Api;

/**
 * ProductRepository CRUD Interface
 */
interface ProductRepositoryInterface
{
    /**
     * Get by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Model\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save Record.
     *
     * @param \Webkul\Marketplace\Model\Product $subject
     * @return \Webkul\Marketplace\Model\Product
     */
    public function save(\Webkul\Marketplace\Model\Product $subject);

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
     * @param \Webkul\Marketplace\Model\Product $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Model\Product $subject);

    /**
     * Delete recod by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
