<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ConditionalDiscountInterface extends ExtensibleDataInterface
{
    public const MAIN_TABLE = 'amasty_mostviewed_pack_conditional_discounts';

    public const ID = 'id';
    public const PACK_ID = 'pack_id';
    public const NUMBER_ITEMS = 'number_items';
    public const DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getPackId(): int;

    /**
     * @param int $packId
     */
    public function setPackId(int $packId): void;

    /**
     * @return int
     */
    public function getNumberItems(): int;

    /**
     * @param int $numberItems
     */
    public function setNumberItems(int $numberItems): void;

    /**
     * @return float
     */
    public function getDiscountAmount(): float;

    /**
     * @param float $discountAmount
     */
    public function setDiscountAmount(float $discountAmount): void;
}
