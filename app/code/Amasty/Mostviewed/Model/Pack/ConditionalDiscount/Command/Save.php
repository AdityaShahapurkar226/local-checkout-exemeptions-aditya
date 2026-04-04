<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var ConditionalDiscountResource
     */
    private $conditionalDiscountResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConditionalDiscountResource $conditionalDiscountResource,
        LoggerInterface $logger
    ) {
        $this->conditionalDiscountResource = $conditionalDiscountResource;
        $this->logger = $logger;
    }

    /**
     * @param ConditionalDiscountInterface|ConditionalDiscount $conditionalDiscount
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(ConditionalDiscountInterface $conditionalDiscount): void
    {
        try {
            $this->conditionalDiscountResource->save($conditionalDiscount);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Conditional Discount'), $e);
        }
    }
}
