<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model;

use Ahy\Ffl\Api\Data\FflInterfaceFactory;
use Ahy\Ffl\Api\Data\FflSearchResultsInterfaceFactory;
use Ahy\Ffl\Api\FflRepositoryInterface;
use Ahy\Ffl\Model\ResourceModel\Ffl as ResourceFfl;
use Ahy\Ffl\Model\ResourceModel\Ffl\CollectionFactory as FflCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class FflRepository implements FflRepositoryInterface
{

    protected $resource;

    protected $fflFactory;

    protected $fflCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataFflFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceFfl $resource
     * @param FflFactory $fflFactory
     * @param FflInterfaceFactory $dataFflFactory
     * @param FflCollectionFactory $fflCollectionFactory
     * @param FflSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceFfl $resource,
        FflFactory $fflFactory,
        FflInterfaceFactory $dataFflFactory,
        FflCollectionFactory $fflCollectionFactory,
        FflSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->fflFactory = $fflFactory;
        $this->fflCollectionFactory = $fflCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFflFactory = $dataFflFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Ahy\Ffl\Api\Data\FflInterface $ffl)
    {
        /* if (empty($ffl->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $ffl->setStoreId($storeId);
        } */
        
        $fflData = $this->extensibleDataObjectConverter->toNestedArray(
            $ffl,
            [],
            \Ahy\Ffl\Api\Data\FflInterface::class
        );
        
        $fflModel = $this->fflFactory->create()->setData($fflData);
        
        try {
            $this->resource->save($fflModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ffl: %1',
                $exception->getMessage()
            ));
        }
        return $fflModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($fflId)
    {
        $ffl = $this->fflFactory->create();
        $this->resource->load($ffl, $fflId);
        if (!$ffl->getId()) {
            throw new NoSuchEntityException(__('Ffl with id "%1" does not exist.', $fflId));
        }
        return $ffl->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->fflCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Ahy\Ffl\Api\Data\FflInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Ahy\Ffl\Api\Data\FflInterface $ffl)
    {
        try {
            $fflModel = $this->fflFactory->create();
            $this->resource->load($fflModel, $ffl->getEntityId());
            $this->resource->delete($fflModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Ffl: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($fflId)
    {
        return $this->delete($this->get($fflId));
    }
}

