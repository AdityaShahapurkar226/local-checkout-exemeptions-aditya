<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Block\Renderer;

use Magento\Framework\View\Layout as NativeLayout;

class Layout extends NativeLayout
{
    /**
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true): string
    {
        return parent::renderElement($name, false);
    }
}
