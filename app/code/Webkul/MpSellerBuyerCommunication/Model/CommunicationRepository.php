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
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\Collection;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;

class CommunicationRepository implements \Webkul\MpSellerBuyerCommunication\Api\CommunicationRepositoryInterface
{
    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication
     */
    protected $_resourceModel;

    /**
     * @param SellerBuyerCommunicationFactory                                                  $communicationFactory
     * @param CollectionFactory                                                                $collectionFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication  $resourceModel
     */
    public function __construct(
        SellerBuyerCommunicationFactory $communicationFactory,
        CollectionFactory $collectionFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication $resourceModel
    ) {

        $this->_resourceModel = $resourceModel;
        $this->_communicationFactory = $communicationFactory;
        $this->_collectionFactory = $collectionFactory;
    }
    
    /**
     * Get collection by entity id
     *
     * @param  integer $entityId
     * @return object
     */
    public function getCollectionByEntityId($entityId)
    {
        $collection = $this->_communicationFactory->create()->load($entityId);

        return $collection;
    }

    /**
     * Get collection by entity id
     *
     * @param  integer $entityId entity id
     * @return object
     */
    public function getAllCollectionByEntityId($entityId)
    {
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter(
                'entity_id',
                [
                    'eq' => $entityId
                ]
            );
        
        return $collection;
    }

    /**
     * Get all queries list by product id
     *
     * @param  int $productId
     * @return object
     */
    public function getAllCollectionByProductId($productId)
    {
        $collection = $this->_communicationFactory->create()->getCollection()
            ->addFieldToFilter(
                'product_id',
                [
                    'eq'=>$productId
                ]
            );
        return $collection;
    }

    /**
     * Get all queries list by seller id
     *
     * @param  int $sellerId
     * @return object
     */
    public function getAllCollectionBySeller($sellerId)
    {
        $collection = $this->_communicationFactory->create()->getCollection()
            ->addFieldToFilter(
                'seller_id',
                [
                    'eq'=>$sellerId
                ]
            );
        return $collection;
    }
}
