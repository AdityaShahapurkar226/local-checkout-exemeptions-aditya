<?php

declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Model;

use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class FormDataProvider extends AbstractDataProvider
{
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data for the form.
     *
     * @return array
     */
    public function getData(): array
    {
        // Object Manager logger
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);

        $logger->info('FormDataProvider::getData called');

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $logger->info('Items count: ' . count($items));

        $this->loadedData = [];

        if (!empty($items)) {
            foreach ($items as $item) {
                // Use the entity ID as key for existing records
                $id = $item->getId();
                $data = $item->getData();

            // Log the data to see if 'state' exists    
            $logger->debug('Dealer ID ' . $id . ' Data: ' . print_r($data, true));
                $this->loadedData[$id] = $item->getData();
                $logger->debug('Dealer Data: ' . print_r($item->getData(), true));
            }
        } else {
            // New entity: key must be null
            $this->loadedData[null] = [
                'dealer_name'         => '',
                'ffl_id'              => '',
                'ffl_expiration_date' => '',
                'street'              => '',
                'city'                => '',
                'state'               => '',
                'zip_code'            => '',
                'is_ffl_active'       => 0, // default active
            ];
            $logger->debug('New Dealer Data: ' . print_r($this->loadedData[null], true));
        }

        $logger->debug('Full Loaded Data: ' . print_r($this->loadedData, true));

        return $this->loadedData;
    }
}