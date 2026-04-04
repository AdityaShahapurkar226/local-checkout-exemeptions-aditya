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
 * Assigned Items Interface
 */
interface ItemsInterface
{

    public const ID = 'id';

    public const PRODUCT_ID = 'product_id';

    public const OWNER_ID = 'owner_id';

    public const SELLER_ID = 'seller_id';

    public const QTY = 'qty';

    public const PRICE = 'price';

    public const DESCRIPTION = 'description';

    public const OPTIONS = 'options';

    public const IMAGE = 'image';

    public const CONDITION = 'condition';

    public const TAX_CLASS = 'tax_class';

    public const TYPE = 'type';

    public const CREATED_AT = 'created_at';

    public const STATUS = 'status';

    public const SHIPPING_COUNTRY_CHARGE = 'shipping_country_charge';

    public const ASSIGN_PRODUCT_ID = 'assign_product_id';

    /**
     * Set Id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
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
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setProductId($productId);
    /**
     * Get ProductId
     *
     * @return int
     */
    public function getProductId();
    /**
     * Set OwnerId
     *
     * @param int $ownerId
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setOwnerId($ownerId);
    /**
     * Get OwnerId
     *
     * @return int
     */
    public function getOwnerId();
    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setSellerId($sellerId);
    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId();
    /**
     * Set Qty
     *
     * @param int $qty
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
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
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setPrice($price);
    /**
     * Get Price
     *
     * @return float
     */
    public function getPrice();
    /**
     * Set Description
     *
     * @param string $description
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setDescription($description);
    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription();
    /**
     * Set Options
     *
     * @param string $options
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setOptions($options);
    /**
     * Get Options
     *
     * @return string
     */
    public function getOptions();
    /**
     * Set Image
     *
     * @param string $image
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setImage($image);
    /**
     * Get Image
     *
     * @return string
     */
    public function getImage();
    /**
     * Set Condition
     *
     * @param int $condition
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setCondition($condition);
    /**
     * Get Condition
     *
     * @return int
     */
    public function getCondition();
    /**
     * Set TaxClass
     *
     * @param int $taxClass
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setTaxClass($taxClass);
    /**
     * Get TaxClass
     *
     * @return int
     */
    public function getTaxClass();
    /**
     * Set Type
     *
     * @param string $type
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setType($type);
    /**
     * Get Type
     *
     * @return string
     */
    public function getType();
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setCreatedAt($createdAt);
    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt();
    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setStatus($status);
    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();
    /**
     * Set ShippingCountryCharge
     *
     * @param string $shippingCountryCharge
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setShippingCountryCharge($shippingCountryCharge);
    /**
     * Get ShippingCountryCharge
     *
     * @return string
     */
    public function getShippingCountryCharge();
    /**
     * Set AssignProductId
     *
     * @param int $assignProductId
     * @return Webkul\MpAssignProduct\Api\Data\ItemsInterface
     */
    public function setAssignProductId($assignProductId);
    /**
     * Get AssignProductId
     *
     * @return int
     */
    public function getAssignProductId();
}
