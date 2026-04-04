<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Product;

class BundlePackWrapper extends BundlePack
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getParentHtml();
    }
}
