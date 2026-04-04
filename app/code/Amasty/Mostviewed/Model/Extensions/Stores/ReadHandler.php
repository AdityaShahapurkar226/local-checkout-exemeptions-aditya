<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Extensions\Stores;

use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\Store\GetByPackId;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var GetByPackId
     */
    private $getByPackId;

    public function __construct(GetByPackId $getByPackId)
    {
        $this->getByPackId = $getByPackId;
    }

    /**
     * @param Pack|object $entity
     * @param array $arguments
     * @return Pack|bool|object|void
     */
    public function execute($entity, $arguments = [])
    {
        $entity->getExtensionAttributes()->setStores(
            $this->getByPackId->execute((int) $entity->getPackId())
        );

        return $entity;
    }
}
