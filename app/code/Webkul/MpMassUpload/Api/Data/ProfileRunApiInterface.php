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


namespace Webkul\MpMassUpload\Api\Data;

/**
 * ProfileRunApi Data Interface
 */
interface ProfileRunApiInterface
{

    public const ID = 'id';

    public const PROFILE_ID = 'profile_id';

    public const ERROR_MESSAGE = 'error_message';

    public const STATUS = 'status';

    public const CREATED_DATE = 'created_date';

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface
     */
    public function setId($id);
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();
    /**
     * Set ProfileId
     *
     * @param int $profileId
     * @return Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface
     */
    public function setProfileId($profileId);
    /**
     * Get ProfileId
     *
     * @return int
     */
    public function getProfileId();
    /**
     * Set ErrorMessage
     *
     * @param string $errorMessage
     * @return Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface
     */
    public function setErrorMessage($errorMessage);
    /**
     * Get ErrorMessage
     *
     * @return string
     */
    public function getErrorMessage();
    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface
     */
    public function setStatus($status);
    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();
    /**
     * Set CreatedDate
     *
     * @param string $createdDate
     * @return Webkul\MpMassUpload\Api\Data\ProfileRunApiInterface
     */
    public function setCreatedDate($createdDate);
    /**
     * Get CreatedDate
     *
     * @return string
     */
    public function getCreatedDate();
}
