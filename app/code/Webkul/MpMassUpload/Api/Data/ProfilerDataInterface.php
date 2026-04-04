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

/**
 * Interface ProfilerDataInterface
 * @api
 * @since 100.0.2
 */
interface ProfilerDataInterface
{
    public const ID             = 'id';

    public const ERROR_MSG      = 'error_msg';

    public const STATUS         = 'status';

    public const PROFILER_NAME  = 'profiler_name';
    
    /**
     * Get Profile Id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Profile Id
     *
     * @param int $id
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setId($id);

    /**
     * Get Profile Error Message
     *
     * @return string
     */
    public function getErrorMsg();

    /**
     * Set Profile Error Message
     *
     * @param string|null $errorMsg
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setErrorMsg($errorMsg);

    /**
     * Get Profile Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Profile Status
     *
     * @param string|null $status
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setStatus($status);

    /**
     * Get Profiler Name
     *
     * @return string
     */
    public function getProfilerName();

    /**
     * Set Profiler Name
     *
     * @param string|null $profileName
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     */
    public function setProfilerName($profileName);
}
