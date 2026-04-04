<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Api\Data;

/**
 * Assigned Product Data Interface
 */
interface DataInterface
{

    public const ID = 'id';

    public const TYPE = 'type';

    public const ASSIGN_ID = 'assign_id';

    public const VALUE = 'value';

    public const DATE = 'date';

    public const IS_DEFAULT = 'is_default';

    public const STATUS = 'status';

    public const STORE_VIEW = 'store_view';

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setId($id);
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();
    /**
     * Set Type
     *
     * @param int $type
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setType($type);
    /**
     * Get Type
     *
     * @return int
     */
    public function getType();
    /**
     * Set AssignId
     *
     * @param int $assignId
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setAssignId($assignId);
    /**
     * Get AssignId
     *
     * @return int
     */
    public function getAssignId();
    /**
     * Set Value
     *
     * @param string $value
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setValue($value);
    /**
     * Get Value
     *
     * @return string
     */
    public function getValue();
    /**
     * Set Date
     *
     * @param string $date
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setDate($date);
    /**
     * Get Date
     *
     * @return string
     */
    public function getDate();
    /**
     * Set IsDefault
     *
     * @param int $isDefault
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setIsDefault($isDefault);
    /**
     * Get IsDefault
     *
     * @return int
     */
    public function getIsDefault();
    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setStatus($status);
    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();
    /**
     * Set StoreView
     *
     * @param int $storeView
     * @return Webkul\MpAssignProduct\Api\Data\DataInterface
     */
    public function setStoreView($storeView);
    /**
     * Get StoreView
     *
     * @return int
     */
    public function getStoreView();
}
