<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Extensions\Stores;

use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\Store\SaveMultiple;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Zend_Db_Exception;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    public function __construct(SaveMultiple $saveMultiple)
    {
        $this->saveMultiple = $saveMultiple;
    }

    /**
     * @param Pack|object $entity
     * @param array $arguments
     * @return Pack|bool|object|void
     * @throws Zend_Db_Exception
     */
    public function execute($entity, $arguments = [])
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $stores = $extensionAttributes->getStores();

        if ($stores !== null) {
            $this->saveMultiple->execute((int)$entity->getPackId(), $stores);
        }

        return $entity;
    }
}
