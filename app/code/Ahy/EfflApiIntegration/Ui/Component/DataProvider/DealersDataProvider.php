<?php
namespace Ahy\EfflApiIntegration\Ui\Component\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer\CollectionFactory;
use Magento\Framework\Api\Filter;

class DealersDataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(Filter $filter)
    {
        if ($filter->getField() && $filter->getValue() !== null) {
            $condition = $filter->getConditionType() ?: 'eq';
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [$condition => $filter->getValue()]
            );
        }
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $this->getCollection()->toArray()['items'],
        ];
    }

    public function getCollection()
    {
        return $this->collection;
    }
}
