<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command;

use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount\DeleteByPackId;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class RemoveByPackId implements RemoveByPackIdInterface
{
    /**
     * @var DeleteByPackId
     */
    private $deleteByPackId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeleteByPackId $deleteByPackId,
        LoggerInterface $logger
    ) {
        $this->deleteByPackId = $deleteByPackId;
        $this->logger = $logger;
    }

    public function execute(int $packId): bool
    {
        try {
            $this->deleteByPackId->execute($packId);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Unable to remove conditional discounts for Pack with ID %1. Error: %2',
                    [$packId, $e->getMessage()]
                )
            );
        }

        return true;
    }
}
