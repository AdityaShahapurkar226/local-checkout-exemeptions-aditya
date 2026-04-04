<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model;

use Ahy\Ffl\Api\Data\FflCentresInterface;
use Ahy\Ffl\Api\Data\FflCentresInterfaceFactory;
use Ahy\Ffl\Api\Data\FflCentresSearchResultsInterfaceFactory;
use Ahy\Ffl\Api\FflCentresRepositoryInterface;
use Ahy\Ffl\Model\ResourceModel\FflCentres as ResourceFflCentres;
use Ahy\Ffl\Model\ResourceModel\FflCentres\CollectionFactory as FflCentresCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FflCentresRepository implements FflCentresRepositoryInterface
{

    /**
     * @var FflCentresInterfaceFactory
     */
    protected $fflCentresFactory;

    /**
     * @var FflCentres
     */
    protected $searchResultsFactory;

    /**
     * @var FflCentresCollectionFactory
     */
    protected $fflCentresCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceFflCentres
     */
    protected $resource;


    /**
     * @param ResourceFflCentres $resource
     * @param FflCentresInterfaceFactory $fflCentresFactory
     * @param FflCentresCollectionFactory $fflCentresCollectionFactory
     * @param FflCentresSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceFflCentres $resource,
        FflCentresInterfaceFactory $fflCentresFactory,
        FflCentresCollectionFactory $fflCentresCollectionFactory,
        FflCentresSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->fflCentresFactory = $fflCentresFactory;
        $this->fflCentresCollectionFactory = $fflCentresCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(FflCentresInterface $fflCentres)
    {
        try {
            $this->resource->save($fflCentres);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the fflCentres: %1',
                $exception->getMessage()
            ));
        }
        return $fflCentres;
    }

    /**
     * @inheritDoc
     */
    public function get($fflCentresId)
    {
        $fflCentres = $this->fflCentresFactory->create();
        $this->resource->load($fflCentres, $fflCentresId);
        if (!$fflCentres->getId()) {
            throw new NoSuchEntityException(__('FflCentres with id "%1" does not exist.', $fflCentresId));
        }
        return $fflCentres;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->fflCentresCollectionFactory->create();
        
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
    public function delete(FflCentresInterface $fflCentres)
    {
        try {
            $fflCentresModel = $this->fflCentresFactory->create();
            $this->resource->load($fflCentresModel, $fflCentres->getFflcentresId());
            $this->resource->delete($fflCentresModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the FflCentres: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($fflCentresId)
    {
        return $this->delete($this->get($fflCentresId));
    }
}

