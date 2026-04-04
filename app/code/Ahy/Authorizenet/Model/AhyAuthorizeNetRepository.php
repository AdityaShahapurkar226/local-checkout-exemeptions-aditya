<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Model;

use Ahy\Authorizenet\Api\AhyAuthorizeNetRepositoryInterface;
use Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface;
use Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterfaceFactory;
use Ahy\Authorizenet\Api\Data\AhyAuthorizeNetSearchResultsInterfaceFactory;
use Ahy\Authorizenet\Model\ResourceModel\AhyAuthorizeNet as ResourceAhyAuthorizeNet;
use Ahy\Authorizenet\Model\ResourceModel\AhyAuthorizeNet\CollectionFactory as AhyAuthorizeNetCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AhyAuthorizeNetRepository implements AhyAuthorizeNetRepositoryInterface
{

    /**
     * @var ResourceAhyAuthorizeNet
     */
    protected $resource;

    /**
     * @var AhyAuthorizeNetInterfaceFactory
     */
    protected $ahyAuthorizeNetFactory;

    /**
     * @var AhyAuthorizeNetCollectionFactory
     */
    protected $ahyAuthorizeNetCollectionFactory;

    /**
     * @var AhyAuthorizeNet
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceAhyAuthorizeNet $resource
     * @param AhyAuthorizeNetInterfaceFactory $ahyAuthorizeNetFactory
     * @param AhyAuthorizeNetCollectionFactory $ahyAuthorizeNetCollectionFactory
     * @param AhyAuthorizeNetSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceAhyAuthorizeNet $resource,
        AhyAuthorizeNetInterfaceFactory $ahyAuthorizeNetFactory,
        AhyAuthorizeNetCollectionFactory $ahyAuthorizeNetCollectionFactory,
        AhyAuthorizeNetSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->ahyAuthorizeNetFactory = $ahyAuthorizeNetFactory;
        $this->ahyAuthorizeNetCollectionFactory = $ahyAuthorizeNetCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        AhyAuthorizeNetInterface $ahyAuthorizeNet
    ) {
        try {
            $this->resource->save($ahyAuthorizeNet);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ahyAuthorizeNet: %1',
                $exception->getMessage()
            ));
        }
        return $ahyAuthorizeNet;
    }

    /**
     * @inheritDoc
     */
    public function get($ahyAuthorizeNetId)
    {
        $ahyAuthorizeNet = $this->ahyAuthorizeNetFactory->create();
        $this->resource->load($ahyAuthorizeNet, $ahyAuthorizeNetId);
        if (!$ahyAuthorizeNet->getId()) {
            throw new NoSuchEntityException(__('AhyAuthorizeNet with id "%1" does not exist.', $ahyAuthorizeNetId));
        }
        return $ahyAuthorizeNet;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ahyAuthorizeNetCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(
        AhyAuthorizeNetInterface $ahyAuthorizeNet
    ) {
        try {
            $ahyAuthorizeNetModel = $this->ahyAuthorizeNetFactory->create();
            $this->resource->load($ahyAuthorizeNetModel, $ahyAuthorizeNet->getAhyauthorizenetId());
            $this->resource->delete($ahyAuthorizeNetModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AhyAuthorizeNet: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($ahyAuthorizeNetId)
    {
        return $this->delete($this->get($ahyAuthorizeNetId));
    }
}

