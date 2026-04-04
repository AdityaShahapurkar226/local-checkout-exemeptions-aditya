<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Model;

use Webkul\Customattribute\Api\Data\ManageAttributeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Customattribute Manageattribute Model
 *
 */
class Manageattribute extends AbstractModel implements ManageAttributeInterface, IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Seller's Statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Seller cache tag
     */
    public const CACHE_TAG = 'attribute_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'attribute_list';

    /**
     * @var string
     */
    protected $_eventPrefix = 'attribute_list';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Customattribute\Model\ResourceModel\Manageattribute::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSeller();
        }
        return parent::load($id, $field);
    }
    
    /**
     * Prepare post's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Active'), self::STATUS_DISABLED => __('Deactive')];
    }

    /**
     * Load No-Route Seller
     *
     * @return \Webkul\Marketplace\Model\Seller
     */
    public function noRouteSeller()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }
    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get AttributeId
     *
     * @return int|null
     */
    public function getAttributeId()
    {
        return parent::getData(self::ATTRIBUTE_ID);
    }
    /**
     * Set AttributeId
     *
     * @param int $attributeId
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }
    /**
     * Set Status
     *
     * @param int $status
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
