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
 * DataflowRepositoryInterface CRUD Interface
 */
interface DataflowRepositoryInterface
{
    /**
     * Get Attribute
     *
     * @return \Magento\Eav\Api\Data\AttributeSetSearchResultsInterface
     */
    public function getAttributeSetList();

    /**
     * Set Attribute
     *
     * @param \Magento\Eav\Api\Data\AttributeSetSearchResultsInterface $attribute
     *
     * @return $this
     */
    public function setAttributeSetList(\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface $attribute);

    /**
     * Save Attribute Set Profile Name
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData
     * @return \Webkul\MpMassUpload\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAttributeSetProfile(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData);

    /**
     * Get AttributeList
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeList(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeSetData);

    /**
     * Get AttributeList
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData
     * @return Webkul\MpMassUpload\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function mapAttributes(\Webkul\MpMassUpload\Api\Data\AttributeInterface $attributeMapData);

    /**
     * Get Attribute Mapped Data
     *
     * @param \Webkul\MpMassUpload\Api\Data\AttributeInterface $mappedData
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMappedAttributeDetails(\Webkul\MpMassUpload\Api\Data\AttributeInterface $mappedData);
    
    /**
     * Get Sample Files
     *
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSampleFiles();

    /**
     * Get Sample Files
     *
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSuperAttributes();

    /**
     * Get Product Export File
     *
     * @param string $productType
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getProductExport($productType);

    /**
     * Delete Profile
     *
     * @param string $profileIds
     * @return void
     */
    public function deleteAttributeProfile($profileIds);

    /**
     * Retrieve Attribute Profile List.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $sellerId
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getAttributeProfileList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $sellerId);

    /**
     * Get Super Atrribute Options
     *
     * @param string $attributeCode
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getSuperAttributeOption($attributeCode);
}
