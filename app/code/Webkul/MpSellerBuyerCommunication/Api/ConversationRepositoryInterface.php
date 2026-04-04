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
namespace Webkul\MpSellerBuyerCommunication\Api;

/**
 * @api
 */
interface ConversationRepositoryInterface
{
    /**
     * Get collection by entity id
     *
     * @param  integer $entityId
     * @return object
     */
    public function getCollectionByEntityId($entityId);

    /**
     * Get collection by query id
     *
     * @param  int $queryId
     * @return object
     */
    public function getCollectionByQueryId($queryId);

    /**
     * Get collection by entity id
     *
     * @param  integer $queryIds
     * @return object
     */
    public function getCollectionByQueryIds($queryIds = []);

    /**
     * Get queryCount
     *
     * @param  object $collection
     * @return int
     */
    public function getQueryCount($collection);

    /**
     * Get reply count
     *
     * @param  object $conv
     * @return int
     */
    public function getReplyCount($conv = []);

    /**
     * Get seller response collection of by query ids
     *
     * @param  array  $queryIds
     * @return object
     */
    public function getResponseCollectionByQueryIds($queryIds = []);
}
