<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Api;

/**
 * api profileRepository Interface
 */
interface ProfileRepositoryInterface
{

    /**
     * Get by id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function getById($id);
    /**
     * Get by id
     *
     * @param object $subject
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function save(\Webkul\MpAssignProduct\Model\Profile $subject);
    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);
    /**
     * Delete
     *
     * @param Webkul\MpAssignProduct\Model\Profile $subject
     * @return boolean
     */
    public function delete(\Webkul\MpAssignProduct\Model\Profile $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
