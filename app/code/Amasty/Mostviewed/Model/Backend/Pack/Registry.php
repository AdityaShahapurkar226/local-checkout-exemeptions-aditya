<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;

class Registry
{
    /**
     * @var PackInterface|null
     */
    private $pack;

    public function set(PackInterface $pack): void
    {
        $this->pack = $pack;
    }

    public function get(): ?PackInterface
    {
        return $this->pack;
    }
}
