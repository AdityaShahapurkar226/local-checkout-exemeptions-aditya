<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Cart;

class ProductAddingProgressFlag
{
    /**
     * @var boolean
     */
    private $flag = false;

    public function get(): bool
    {
        return $this->flag;
    }

    public function set(bool $flag): void
    {
        $this->flag = $flag;
    }
}
