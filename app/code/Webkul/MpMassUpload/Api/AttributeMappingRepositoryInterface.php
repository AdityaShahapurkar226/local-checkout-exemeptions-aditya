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
 * MpMassUpload Attribute Mapping CRUD interface.
 */
interface AttributeMappingRepositoryInterface
{
    /**
     * Retrieve MpMassUpload Attribute Mapping Data By Id.
     *
     * @api
     * @param string $entityId
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve MpMassUpload Attribute Mapping Data By profile id.
     *
     * @api
     * @param int $profileId
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Profile Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProfileId($profileId);

    /**
     * Retrieve MpMassUpload Attribute Mapping Data By uploaded file attribute name.
     *
     * @api
     * @param int $profileId
     * @param int $fileAttribute
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByFileAttribute($profileId, $fileAttribute);

    /**
     * Retrieve MpMassUpload Attribute Mapping Data By uploaded magento attribute name.
     *
     * @api
     * @param int $profileId
     * @param int $mageAttribute
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMageAttribute($profileId, $mageAttribute);

    /**
     * Retrieve MpMassUpload Attribute Mapping Collection.
     *
     * @api
     * @return \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();

    /**
     * Delete MpMassUpload Attribute Mapping.
     *
     * @api
     * @param \Webkul\MpMassUpload\Api\Data\AttributeMappingInterface $attributeMapping
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\MpMassUpload\Api\Data\AttributeMappingInterface $attributeMapping);

    /**
     * Delete MpMassUpload Attribute Mapping by ID.
     *
     * @api
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Delete MpMassUpload Attribute Mapping by Attribute Mapping ID.
     *
     * @api
     * @param int $profileId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByProfileId($profileId);
}
