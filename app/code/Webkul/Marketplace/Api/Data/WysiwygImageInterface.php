<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Api\Data;

interface WysiwygImageInterface
{
    public const ENTITY_ID   = 'entity_id';

    public const SELLER_ID   = 'seller_id';

    public const URL         = 'url';

    public const NAME        = 'name';

    public const TYPE        = 'type';

    public const FILE        = 'file';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Url
     *
     * @return string|null
     */
    public function getUrl();

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get Type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get File
     *
     * @return string|null
     */
    public function getFile();

    /**
     * Set ID
     *
     * @param int $id
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Seller Id
     *
     * @param int $sellerId
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Url
     *
     * @param string $url
     * @return string|null
     */
    public function setUrl($url);

    /**
     * Set Name
     *
     * @param string $name
     * @return string|null
     */
    public function setName($name);

    /**
     * Set Type
     *
     * @param string $type
     * @return string|null
     */
    public function setType($type);

    /**
     * Set File
     *
     * @param mixed $file
     * @return string|null
     */
    public function setFile($file);
}
