<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterfaceFactory;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetById implements GetByIdInterface
{
    /**
     * @var ConditionalDiscountInterfaceFactory
     */
    private $conditionalDiscountFactory;

    /**
     * @var ConditionalDiscountResource
     */
    private $conditionalDiscountResource;

    public function __construct(
        ConditionalDiscountInterfaceFactory $conditionalDiscountFactory,
        ConditionalDiscountResource $conditionalDiscountResource
    ) {
        $this->conditionalDiscountFactory = $conditionalDiscountFactory;
        $this->conditionalDiscountResource = $conditionalDiscountResource;
    }

    /**
     * @param int $id
     * @return ConditionalDiscountInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): ConditionalDiscountInterface
    {
        /** @var ConditionalDiscountInterface|ConditionalDiscount $conditionalDiscount */
        $conditionalDiscount = $this->conditionalDiscountFactory->create();
        $this->conditionalDiscountResource->load($conditionalDiscount, $id);

        if ($conditionalDiscount->getId() === null) {
            throw new NoSuchEntityException(
                __('Conditional Discount with id "%value" does not exist.', ['value' => $id])
            );
        }

        return $conditionalDiscount;
    }
}
