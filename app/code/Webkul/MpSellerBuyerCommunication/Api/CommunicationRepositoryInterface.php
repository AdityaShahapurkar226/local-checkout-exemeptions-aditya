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
interface CommunicationRepositoryInterface
{
    /**
     * Get collection by entity id
     *
     * @param  integer $entityId
     * @return object
     */
    public function getCollectionByEntityId($entityId);

    /**
     * Get all queries list by product id
     *
     * @param  int $productId
     * @return object
     */
    public function getAllCollectionByProductId($productId);

    /**
     * Get all queries list by product id
     *
     * @param  int $sellerId
     * @return object
     */
    public function getAllCollectionBySeller($sellerId);
}
