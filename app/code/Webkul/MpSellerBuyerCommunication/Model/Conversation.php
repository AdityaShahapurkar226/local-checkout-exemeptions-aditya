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

use Webkul\MpSellerBuyerCommunication\Api\Data\ConversationInterface;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Conversation Model
 *
 * @method \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation _getResource()
 * @method \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation getResource()
 */
class Conversation extends \Magento\Framework\Model\AbstractModel implements ConversationInterface, IdentityInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * communication table name
     */
    public const TABLE_NAME = 'marketplace_sellerbuyercommunication';

    /**
     * status approved value
     */
    public const STATUS_APPROVE = '1';

    /**
     * status disapprove value
     */
    public const STATUS_DISAPPROVE = '0';
    /**
     * MpSellerBuyerCommunication Conversation cache tag
     */
    public const CACHE_TAG = 'marketplace_sellerbuyercommunication_conversation';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_sellerbuyercommunication_conversation';

    /**
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_sellerbuyercommunication_conversation';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation::class);
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
            return $this->noRouteConversation();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Conversation
     *
     * @return \Webkul\MpSellerBuyerCommunication\Model\Conversation
     */
    public function noRouteConversation()
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
     * @return \Webkul\MpSellerBuyerCommunication\Api\Data\ConversationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
