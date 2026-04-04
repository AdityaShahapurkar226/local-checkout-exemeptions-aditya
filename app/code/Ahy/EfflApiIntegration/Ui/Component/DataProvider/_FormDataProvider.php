<?php
declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Ui\Component\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer\CollectionFactory as DealerCollectionFactory;

class FormDataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        DealerCollectionFactory $collectionFactory, // updated type-hint
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create(); // initialize collection
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return form data.
     * Return empty array to get a blank form when adding a new dealer.
     */
    public function getData()
    {
        return [
            'totalRecords' => 0,
            'items' => []
        ];
    }
}
