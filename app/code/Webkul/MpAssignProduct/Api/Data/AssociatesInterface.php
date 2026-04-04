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
 * Configurable Assigned product Associates Interface
 */
interface AssociatesInterface
{

    public const ID = 'id';

    public const PRODUCT_ID = 'product_id';

    public const PARENT_ID = 'parent_id';

    public const PARENT_PRODUCT_ID = 'parent_product_id';

    public const QTY = 'qty';

    public const PRICE = 'price';

    public const OPTIONS = 'options';

    public const ASSIGN_PRODUCT_ID = 'assign_product_id';

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setId($id);
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();
    /**
     * Set ProductId
     *
     * @param int $productId
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setProductId($productId);
    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId();
    /**
     * Set ParentId
     *
     * @param int $parentId
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setParentId($parentId);
    /**
     * Get ParentId
     *
     * @return int
     */
    public function getParentId();
    /**
     * Set ParentProductId
     *
     * @param int $parentProductId
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setParentProductId($parentProductId);
    /**
     * Get ParentProductId
     *
     * @return int
     */
    public function getParentProductId();
    /**
     * Set Qty
     *
     * @param int $qty
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setQty($qty);
    /**
     * Get Qty
     *
     * @return int
     */
    public function getQty();
    /**
     * Set Price
     *
     * @param float $price
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setPrice($price);
    /**
     * Get Price
     *
     * @return float
     */
    public function getPrice();
    /**
     * Set Options
     *
     * @param string $options
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setOptions($options);
    /**
     * Get Options
     *
     * @return string
     */
    public function getOptions();
    /**
     * Set AssignProductId
     *
     * @param int $assignProductId
     * @return Webkul\MpAssignProduct\Api\Data\AssociatesInterface
     */
    public function setAssignProductId($assignProductId);
    /**
     * Get AssignProductId
     *
     * @return int
     */
    public function getAssignProductId();
}
