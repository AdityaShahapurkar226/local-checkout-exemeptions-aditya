<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class ConditionalStrategy implements RetrieveStrategyInterface
{
    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    public function __construct(PackRepositoryInterface $packRepository)
    {
        $this->packRepository = $packRepository;
    }

    public function execute(AbstractItem $item, SimplePack $simplePack): float
    {
        $pack = $this->packRepository->getById($simplePack->getComplexPack()->getPackId());
        $itemsQty = $simplePack->getItemsCount();
        $conditionalDiscounts = $pack->getExtensionAttributes()->getConditionalDiscounts() ?: [];
        foreach ($conditionalDiscounts as $conditionalDiscount) {
            if ($itemsQty < $conditionalDiscount->getNumberItems()) {
                break;
            }
            $discountAmount = $conditionalDiscount->getDiscountAmount();
        }

        return $discountAmount ?? 0;
    }
}
