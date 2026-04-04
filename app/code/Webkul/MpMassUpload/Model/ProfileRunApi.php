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

namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface;

/**
 * ProfileRunApi Class CURD
 */
class ProfileRunApi extends AbstractModel implements IdentityInterface, ProfileRunApiInterface
{

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_MpMassUpload_profilerunapi';

   /**
    * @var string
    */
    protected $_cacheTag = 'webkul_MpMassUpload_profilerunapi';

    /**
     * @var string
     */
    protected $_eventPrefix = 'webkul_MpMassUpload_profilerunapi';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpMassUpload\Model\ResourceModel\ProfileRunApi::class);
    }

    /**
     * Load No-Route Indexer.
     *
     * @return $this
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpMassUpload\Model\ProfileRunApiInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Set ProfileId
     *
     * @param int $profileId
     * @return Webkul\MpMassUpload\Model\ProfileRunApiInterface
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * Get ProfileId
     *
     * @return int
     */
    public function getProfileId()
    {
        return parent::getData(self::PROFILE_ID);
    }

    /**
     * Set ErrorMessage
     *
     * @param string $errorMessage
     * @return Webkul\MpMassUpload\Model\ProfileRunApiInterface
     */
    public function setErrorMessage($errorMessage)
    {
        return $this->setData(self::ERROR_MESSAGE, $errorMessage);
    }

    /**
     * Get ErrorMessage
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return parent::getData(self::ERROR_MESSAGE);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpMassUpload\Model\ProfileRunApiInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set CreatedDate
     *
     * @param string $createdDate
     * @return Webkul\MpMassUpload\Model\ProfileRunApiInterface
     */
    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }

    /**
     * Get CreatedDate
     *
     * @return string
     */
    public function getCreatedDate()
    {
        return parent::getData(self::CREATED_DATE);
    }
}
