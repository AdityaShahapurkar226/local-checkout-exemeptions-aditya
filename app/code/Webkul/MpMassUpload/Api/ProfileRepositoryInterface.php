<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Api;

/**
 * MpMassUpload Profile CRUD interface.
 */
interface ProfileRepositoryInterface
{
    /**
     * Retrieve MpMassUpload Profile Data By Id.
     *
     * @api
     * @param string $entityId
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve MpMassUpload Profile Data By seller id.
     *
     * @api
     * @param int $sellerId
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Seller Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySellerId($sellerId);

    /**
     * Retrieve MpMassUpload Profile Data By Seller id and profile name.
     *
     * @api
     * @param int $sellerId
     * @param int $profileName
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProfileName($sellerId, $profileName);

    /**
     * Retrieve MpMassUpload Profile Collection.
     *
     * @api
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();

    /**
     * Delete MpMassUpload Profile.
     *
     * @api
     * @param \Webkul\MpMassUpload\Api\Data\ProfileInterface $profile
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\MpMassUpload\Api\Data\ProfileInterface $profile);

    /**
     * Delete MpMassUpload Profile by ID.
     *
     * @api
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Delete MpMassUpload Profile by Profile ID.
     *
     * @api
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteBySellerId($sellerId);

    /**
     * Delete MpMassUpload Profile by Profile ID.
     *
     * @api
     * @param int $sellerId
     * @param int $profileName
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProfileName($sellerId, $profileName);
}
