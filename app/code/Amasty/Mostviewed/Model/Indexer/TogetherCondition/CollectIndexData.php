<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition;

use Amasty\Mostviewed\Model\Indexer\TogetherCondition\Specification\ConditionSpecification;
use Exception;
use Psr\Log\LoggerInterface;

class CollectIndexData
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConditionSpecification[]
     */
    private $togetherConditions;

    public function __construct(Indexer $indexer, LoggerInterface $logger, array $togetherConditions = [])
    {
        $this->indexer = $indexer;
        $this->logger = $logger;
        $this->togetherConditions = $togetherConditions;
    }

    public function execute(): void
    {
        foreach ($this->togetherConditions as $togetherCondition) {
            try {
                $this->indexer->reindex($togetherCondition);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
