<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Order;

use Amasty\Mostviewed\Api\Data\PackInterface;

class OrderPack
{
    /**
     * @var PackInterface
     */
    private $pack;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var string
     */
    private $packName;

    public function getQty(): int
    {
        return $this->qty;
    }

    public function setQty(int $qty): void
    {
        $this->qty = $qty;
    }

    public function getPack(): ?PackInterface
    {
        return $this->pack;
    }

    public function setPack(?PackInterface $pack): void
    {
        $this->pack = $pack;
    }

    public function getPackName(): string
    {
        return $this->packName;
    }

    public function setPackName(string $packName): void
    {
        $this->packName = $packName;
    }
}
