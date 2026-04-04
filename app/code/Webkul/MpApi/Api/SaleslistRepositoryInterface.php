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
 * SaleslistRepository CRUD Interface
 */
interface SaleslistRepositoryInterface
{
    /**
     * Get record by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save record.
     *
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterface $subject
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     */
    public function save(\Webkul\Marketplace\Api\Data\SaleslistInterface $subject);

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
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterface $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\SaleslistInterface $subject);

    /**
     * Delete record by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
