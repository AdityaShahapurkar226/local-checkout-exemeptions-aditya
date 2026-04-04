<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder\Result;

class ComplexPack
{
    /**
     * @var int|null
     */
    private $packId;

    /**
     * @var int
     */
    private $packQty = 0;

    /**
     * @var SimplePack[]
     */
    private $packs;

    public function getPackId(): ?int
    {
        return $this->packId;
    }

    public function setPackId(int $packId): void
    {
        $this->packId = $packId;
    }

    /**
     * @param SimplePack[] $packs
     */
    public function setPacks(array $packs): void
    {
        $this->clearPackQty();
        foreach ($packs as $pack) {
            $this->addPackQty($pack->getPackQty());
            $pack->setComplexPack($this);
        }
        $this->packs = $packs;
    }

    public function addPack(SimplePack $pack): void
    {
        $this->addPackQty($pack->getPackQty());
        $this->packs[] = $pack;
    }

    /**
     * @return SimplePack[]
     */
    public function getPacks(): array
    {
        return $this->packs;
    }

    private function addPackQty(int $qty): void
    {
        $this->packQty += $qty;
    }

    private function clearPackQty(): void
    {
        $this->packQty = 0;
    }

    public function getPackQty(): int
    {
        return $this->packQty;
    }
}
