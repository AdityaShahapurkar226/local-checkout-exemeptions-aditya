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
namespace Webkul\MpMassUpload\Api\Data;

interface ProfileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'id';
    
    public const SELLER_ID = 'customer_id';

    public const PROFILE_NAME = 'profile_name';

    public const PRODUCT_TYPE = 'product_type';

    public const ATTRIBUTE_SET_ID = 'attribute_set_id';

    public const IMAGE_FILE = 'image_file';

    public const LINK_FILE = 'link_file';

    public const SAMPLE_FILE = 'sample_files';

    public const DATA_ROW = 'data_row';

    public const FILE_TYPE = 'file_type';

    public const CREATED_DATE = 'created_date';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setId($id);

    /**
     * Get Seller Id.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Seller Id.
     *
     * @param int $sellerId
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setCustomerId($sellerId);

    /**
     * Get Profile Name.
     *
     * @return string|null
     */
    public function getProfileName();

    /**
     * Set Profile Name.
     *
     * @param string $profileName
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setProfileName($profileName);

    /**
     * Get Product Type.
     *
     * @return string|null
     */
    public function getProductType();

    /**
     * Set Product Type.
     *
     * @param string $productType
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setProductType($productType);

    /**
     * Get Attribute Set Id.
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Set Attribute Set Id.
     *
     * @param int $attributeSetId
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Get Image File.
     *
     * @return string|null
     */
    public function getImageFile();

    /**
     * Set Image File.
     *
     * @param string $imageFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setImageFile($imageFile);

    /**
     * Get Link File.
     *
     * @return string|null
     */
    public function getLinkFile();

    /**
     * Set Link File.
     *
     * @param string $linkFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setLinkFile($linkFile);

    /**
     * Get Sample File.
     *
     * @return string|null
     */
    public function getSampleFile();

    /**
     * Set Sample File.
     *
     * @param string $sampleFile
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setSampleFile($sampleFile);

    /**
     * Get Data Row.
     *
     * @return string|null
     */
    public function getDataRow();

    /**
     * Set Data Row.
     *
     * @param string $dataRow
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setDataRow($dataRow);

    /**
     * Get File Type.
     *
     * @return string|null
     */
    public function getFileType();

    /**
     * Set File Type.
     *
     * @param string $fileType
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setFileType($fileType);

    /**
     * Get Created Date.
     *
     * @return date|null
     */
    public function getCreatedDate();

    /**
     * Set Created Date.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpMassUpload\Api\Data\ProfileInterface
     */
    public function setCreatedDate($createdDate);
}
