<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Exception;
use Magento\Framework\Exception\LocalizedException;

class DeleteByPackId
{
    /**
     * @var ConditionalDiscountResource
     */
    private $conditionalDiscountResource;

    public function __construct(ConditionalDiscountResource $conditionalDiscountResource)
    {
        $this->conditionalDiscountResource = $conditionalDiscountResource;
    }

    /**
     * @param int $packId
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(int $packId): void
    {
        $this->conditionalDiscountResource->getConnection()->delete(
            $this->conditionalDiscountResource->getMainTable(),
            [sprintf('%s = ?', ConditionalDiscountInterface::PACK_ID) => $packId]
        );
    }
}
