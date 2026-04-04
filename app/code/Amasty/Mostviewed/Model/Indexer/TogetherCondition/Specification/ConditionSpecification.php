<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition\Specification;

use Amasty\Mostviewed\Model\Indexer\TogetherCondition\Loader\LoaderInterface;

class ConditionSpecification
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $replicaTableName;

    /**
     * @var LoaderInterface
     */
    private $loader;

    public function __construct(string $tableName, string $replicaTableName, LoaderInterface $loader)
    {
        $this->tableName = $tableName;
        $this->replicaTableName = $replicaTableName;
        $this->loader = $loader;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getReplicaTableName(): string
    {
        return $this->replicaTableName;
    }

    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }
}
