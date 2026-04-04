<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Model;

use Webkul\MpSellerBuyerCommunication\Api\Data\SellerBuyerCommunicationInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * SellerBuyerCommunication Model
 *
 * @method \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication _getResource()
 * @method \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication getResource()
 */
class SellerBuyerCommunication extends \Magento\Framework\Model\AbstractModel implements
    SellerBuyerCommunicationInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace SellerBuyerCommunication cache tag
     */
    public const CACHE_TAG = 'marketplace_sellerbuyercommunication';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_sellerbuyercommunication';

    /**
     * @var string
     */
    protected $_eventPrefix = 'marketplace_sellerbuyercommunication';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication::class);
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
            return $this->noRouteSellerBuyerCommunication();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route SellerBuyerCommunication
     *
     * @return \Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication
     */
    public function noRouteSellerBuyerCommunication()
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
     * @return \Webkul\MpSellerBuyerCommunication\Api\Data\SellerBuyerCommunicationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
