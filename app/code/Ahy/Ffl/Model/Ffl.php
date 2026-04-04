<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model;

use Ahy\Ffl\Api\Data\FflInterface;
use Ahy\Ffl\Api\Data\FflInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Ffl extends \Magento\Framework\Model\AbstractModel
{

    const ENTITY = 'ahy_ffl_entity';
    protected $fflDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'ahy_ffl_entity';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param FflInterfaceFactory $fflDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Ahy\Ffl\Model\ResourceModel\Ffl $resource
     * @param \Ahy\Ffl\Model\ResourceModel\Ffl\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        FflInterfaceFactory $fflDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Ahy\Ffl\Model\ResourceModel\Ffl $resource,
        \Ahy\Ffl\Model\ResourceModel\Ffl\Collection $resourceCollection,
        array $data = []
    ) {
        $this->fflDataFactory = $fflDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve ffl model with ffl data
     * @return FflInterface
     */
    public function getDataModel()
    {
        $fflData = $this->getData();
        
        $fflDataObject = $this->fflDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $fflDataObject,
            $fflData,
            FflInterface::class
        );
        
        return $fflDataObject;
    }
}

