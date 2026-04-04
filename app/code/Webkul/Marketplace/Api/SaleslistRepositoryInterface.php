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
 * SaleslistRepository CRUD Interface
 */
interface SaleslistRepositoryInterface
{
    /**
     * Get record by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Model\Saleslist
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save record.
     *
     * @param \Webkul\Marketplace\Model\Saleslist $subject
     * @return \Webkul\Marketplace\Model\Saleslist
     */
    public function save(\Webkul\Marketplace\Model\Saleslist $subject);

    /**
     * Get list.
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete record.
     *
     * @param \Webkul\Marketplace\Model\Saleslist $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Model\Saleslist $subject);

    /**
     * Delete record by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
