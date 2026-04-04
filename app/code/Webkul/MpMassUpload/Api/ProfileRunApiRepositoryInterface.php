<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Api;

/**
 * ProfileRunApiRepository Data Interface
 */
interface ProfileRunApiRepositoryInterface
{

    /**
     * Set by id
     *
     * @param int $id
     * @return \Webkul\MpMassUpload\Model\ProfileRunApi
     */
    public function getById($id);

    /**
     * Set by id
     *
     * @param \Webkul\MpMassUpload\Model\ProfileRunApi $subject
     * @return \Webkul\MpMassUpload\Model\ProfileRunApi
     */
    public function save(\Webkul\MpMassUpload\Model\ProfileRunApi $subject);

    /**
     * Set list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete
     *
     * @param \Webkul\MpMassUpload\Model\ProfileRunApi $subject
     * @return boolean
     */
    public function delete(\Webkul\MpMassUpload\Model\ProfileRunApi $subject);

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
