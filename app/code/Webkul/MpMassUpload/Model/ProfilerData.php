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

use \Magento\Framework\DataObject\IdentityInterface;
use \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * ProfilerData Class Getter Setter
 */
class ProfilerData extends AbstractModel implements IdentityInterface, ProfilerDataInterface
{

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
     * Set Profile Id
     *
     * @param int $id
     * @return Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get Profile Id
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Set Profile Error Message
     *
     * @param string|null $errorMsg
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setErrorMsg($errorMsg)
    {
        return $this->setData(self::ERROR_MSG, $errorMsg);
    }

    /**
     * Get Profile Error Message
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return parent::getData(self::ERROR_MSG);
    }

    /**
     * Set Profile Status
     *
     * @param string|null $status
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Profile Status
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Get Profiler Name
     *
     * @return string
     */
    public function getProfilerName()
    {
        return parent::getData(self::PROFILER_NAME);
    }

    /**
     * Set Profiler Name
     *
     * @param string|null $profileName
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setProfilerName($profileName)
    {
        return $this->setData(self::PROFILER_NAME, $profileName);
    }
}
