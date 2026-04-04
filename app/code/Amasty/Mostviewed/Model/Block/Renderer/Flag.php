<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Block\Renderer;

/**
 * Used for detecting when layout used for render blocks for mostviewed.
 */
class Flag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function isActive(): bool
    {
        return $this->flag;
    }

    public function enable(): void
    {
        $this->flag = true;
    }

    public function disable(): void
    {
        $this->flag = false;
    }
}
