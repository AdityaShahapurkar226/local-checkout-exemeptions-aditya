<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Api;

/**
 * api profileRepository Interface
 */
interface ProfileRepositoryInterface
{

    /**
     * get by id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function getById($id);
    /**
     * get by id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function save(\Webkul\MpAssignProduct\Model\Profile $subject);
    /**
     * get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);
    /**
     * delete
     *
     * @param Webkul\MpAssignProduct\Model\Profile $subject
     * @return boolean
     */
    public function delete(\Webkul\MpAssignProduct\Model\Profile $subject);
    /**
     * delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
