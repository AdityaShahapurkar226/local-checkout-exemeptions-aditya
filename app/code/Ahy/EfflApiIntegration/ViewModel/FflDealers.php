<?php

namespace Ahy\EfflApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer\CollectionFactory;

class FflDealers implements ArgumentInterface
{
    protected CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get all dealers from ahy_orchid_ffl_dealers
     */
    public function getAllDealers()
    {
        $collection = $this->collectionFactory->create();
        return $collection->getItems();
    }
}
