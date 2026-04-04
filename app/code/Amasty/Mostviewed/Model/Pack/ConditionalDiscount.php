<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Magento\Framework\Model\AbstractExtensibleModel;

class ConditionalDiscount extends AbstractExtensibleModel implements ConditionalDiscountInterface
{
    public function _construct()
    {
        $this->_init(ConditionalDiscountResource::class);
    }

    public function getPackId(): int
    {
        return (int) $this->_getData(ConditionalDiscountInterface::PACK_ID);
    }

    public function setPackId(int $packId): void
    {
        $this->setData(ConditionalDiscountInterface::PACK_ID, $packId);
    }

    public function getNumberItems(): int
    {
        return (int) $this->_getData(ConditionalDiscountInterface::NUMBER_ITEMS);
    }

    public function setNumberItems(int $numberItems): void
    {
        $this->setData(ConditionalDiscountInterface::NUMBER_ITEMS, $numberItems);
    }

    public function getDiscountAmount(): float
    {
        return (float) $this->_getData(ConditionalDiscountInterface::DISCOUNT_AMOUNT);
    }

    public function setDiscountAmount(float $discountAmount): void
    {
        $this->setData(ConditionalDiscountInterface::DISCOUNT_AMOUNT, $discountAmount);
    }
}
