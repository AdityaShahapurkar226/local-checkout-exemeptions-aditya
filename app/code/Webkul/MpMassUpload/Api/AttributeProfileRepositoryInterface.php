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
 * MpMassUpload AttributeProfile CRUD interface.
 */
interface AttributeProfileRepositoryInterface
{
    /**
     * Retrieve MpMassUpload AttributeProfile Data By Id.
     *
     * @api
     * @param string $entityId
     * @return \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve MpMassUpload AttributeProfile Data By seller id.
     *
     * @api
     * @param int $sellerId
     * @return \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Seller Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySellerId($sellerId);

    /**
     * Retrieve MpMassUpload AttributeProfile Data By Seller id and profile name.
     *
     * @api
     * @param int $sellerId
     * @param int $profileName
     * @return \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProfileName($sellerId, $profileName);

    /**
     * Retrieve MpMassUpload AttributeProfile Collection.
     *
     * @api
     * @return \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();

    /**
     * Delete MpMassUpload AttributeProfile.
     *
     * @api
     * @param \Webkul\MpMassUpload\Api\Data\AttributeProfileInterface $attributeProfile
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\MpMassUpload\Api\Data\AttributeProfileInterface $attributeProfile);

    /**
     * Delete MpMassUpload AttributeProfile by ID.
     *
     * @api
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Delete MpMassUpload AttributeProfile by AttributeProfile ID.
     *
     * @api
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteBySellerId($sellerId);

    /**
     * Delete MpMassUpload AttributeProfile by AttributeProfile ID.
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
