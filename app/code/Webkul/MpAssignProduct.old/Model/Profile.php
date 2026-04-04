<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\MpAssignProduct\Api\Data\ProfileInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Profile extends AbstractModel implements ProfileInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpAssignProduct Profile cache tag.
     */
    const CACHE_TAG = 'mpassignproduct_profile';

    /**
     * @var string
     */
    protected $_cacheTag = 'mpassignproduct_profile';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mpassignproduct_profile';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpAssignProduct\Model\ResourceModel\Profile::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProfile();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Profile.
     *
     * @return \Webkul\MpAssignProduct\Model\Profile
     */
    public function noRouteProfile()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getEntityId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Seller Id.
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set Seller Id.
     *
     * @param int $sellerId
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get Csv File.
     *
     * @return string|null
     */
    public function getCsvFile()
    {
        return parent::getData(self::CSV_FILE);
    }

    /**
     * Set Csv File.
     *
     * @param string $csvFile
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setCsvFile($csvFile)
    {
        return $this->setData(self::CSV_FILE, $csvFile);
    }

    /**
     * Get Profile Name.
     *
     * @return string|null
     */
    public function getProfileName()
    {
        return parent::getData(self::PROFILE_NAME);
    }

    /**
     * Set Profile Name.
     *
     * @param string $profileName
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setProfileName($profileName)
    {
        return $this->setData(self::PROFILE_NAME, $profileName);
    }

    /**
     * Get Product Type.
     *
     * @return string|null
     */
    public function getProductType()
    {
        return parent::getData(self::PRODUCT_TYPE);
    }

    /**
     * Set Product Type.
     *
     * @param string $productType
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * Get Time.
     *
     * @return string|null
     */
    public function getTime()
    {
        return parent::getData(self::TIME);
    }

    /**
     * Set Time.
     *
     * @param string $time
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setTime($time)
    {
        return $this->setData(self::TIME, $time);
    }

    /**
     * Get Status.
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set Status.
     *
     * @param string $status
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Image File.
     *
     * @return string|null
     */
    public function getImageFile()
    {
        return parent::getData(self::IMAGE_FILE);
    }

    /**
     * Set Image File.
     *
     * @param string $imageFile
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setImageFile($imageFile)
    {
        return $this->setData(self::IMAGE_FILE, $imageFile);
    }

    /**
     * Get Data Row.
     *
     * @return string|null
     */
    public function getDataRow()
    {
        return parent::getData(self::DATA_ROW);
    }

    /**
     * Set Data Row.
     *
     * @param string $dataRow
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setDataRow($dataRow)
    {
        return $this->setData(self::DATA_ROW, $dataRow);
    }

    /**
     * Get File Type.
     *
     * @return string|null
     */
    public function getFileType()
    {
        return parent::getData(self::FILE_TYPE);
    }

    /**
     * Set File Type.
     *
     * @param string $fileType
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setFileType($fileType)
    {
        return $this->setData(self::FILE_TYPE, $fileType);
    }

    /**
     * Get Created Date.
     *
     * @return date|null
     */
    public function getCreatedDate()
    {
        return parent::getData(self::CREATED_DATE);
    }

    /**
     * Set Created Date.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpAssignProduct\Api\Data\ProfileInterface
     */
    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }
}
