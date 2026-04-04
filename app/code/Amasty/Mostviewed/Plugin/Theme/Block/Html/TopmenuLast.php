<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Theme\Block\Html;

use Amasty\Mostviewed\Model\OptionSource\TopMenuLink;

class TopmenuLast extends Topmenu
{
    /**
     * @return int
     */
    protected function getPosition()
    {
        return TopMenuLink::DISPLAY_LAST;
    }
}
