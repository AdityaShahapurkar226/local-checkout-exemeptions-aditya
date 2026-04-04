<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Api\Data;

/**
 * Upload profile interface
 */
interface ProfileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';
    public const SELLER_ID = 'seller_id';
    public const CSV_FILE = 'csv_file';
    public const PROFILE_NAME = 'profile_name';
    public const PRODUCT_TYPE = 'product_type';
    public const TIME = 'time';
    public const STATUS = 'status';
    public const CREATED_DATE = 'created_date';
    public const IMAGE_FILE = 'image_file';
    public const DATA_ROW = 'data_row';
    public const FILE_TYPE = 'file_type';

    /**#@-*/

    /**
     * Get Entity ID.
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity ID.
     *
     * @param int $id
     *
     * @return void
     */
    public function setEntityId($id);

    /**
     * Get Seller ID.
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set Seller ID.
     *
     * @param int $sellerId
     *
     * @return void
     */
    public function setSellerId($sellerId);

    /**
     * Get Csv File.
     *
     * @return string|null
     */
    public function getCsvFile();

    /**
     * Set Csv File.
     *
     * @param string $csvFile
     *
     * @return void
     */
    public function setCsvFile($csvFile);

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
     * @return void
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
     * @return void
     */
    public function setProductType($productType);

    /**
     * Get Time.
     *
     * @return string|null
     */
    public function getTime();

    /**
     * Set Time.
     *
     * @param string $time
     *
     * @return void
     */
    public function setTime($time);

    /**
     * Get Status.
     *
     * @return Bool|null
     */
    public function getStatus();

    /**
     * Set Status.
     *
     * @param bool $status
     *
     * @return void
     */
    public function setStatus($status);

    /**
     * Get Created Date.
     *
     * @return string|null
     */
    public function getCreatedDate();

    /**
     * Set Created Date.
     *
     * @param string $createdDate
     *
     * @return void
     */
    public function setCreatedDate($createdDate);

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
     * @return void
     */
    public function setImageFile($imageFile);

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
     * @return void
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
     * @return void
     */
    public function setFileType($fileType);
}
