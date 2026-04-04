<?php
namespace Ahy\EfflApiIntegration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Psr\Log\LoggerInterface;

class State implements OptionSourceInterface
{
    protected $regionCollectionFactory;
    protected $logger;

    public function __construct(
        RegionCollectionFactory $regionCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->logger = $logger;
    }

    public function toOptionArray()
    {
        $options = [];

        $options[] = [
                'value' => '',
                'label' => __('-- Select State --')
            ];
        $regions = $this->regionCollectionFactory->create()
            ->addFieldToSelect(['region_id', 'code', 'default_name'])
            ->addFieldToFilter('country_id', 'US')
            ->setOrder('default_name', 'ASC');

        foreach ($regions as $region) {
            $options[] = [
                'value' => $region->getCode(),        
                'label' => $region->getDefaultName()  
            ];
        }

        if (empty($options)) {
            $options[] = ['value' => 'TEST1', 'label' => 'Test Option 1'];
            $options[] = ['value' => 'TEST2', 'label' => 'Test Option 2'];
        }

        return $options;
    }

}
