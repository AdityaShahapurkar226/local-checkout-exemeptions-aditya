<?php

declare(strict_types=1);

namespace Ewave\ExtendedBundleProduct\Block\DataProviders;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Block\DataProviders\OptionPriceRenderer as ParentOptionPriceRenderer;

/**
 * Provides additional data for bundle options
 */
class OptionPriceRenderer extends ParentOptionPriceRenderer
{
    /**
     * Format tier price string
     *
     * @param Product $selection
     * @param array $arguments
     * @return string
     */
    public function renderTierPrice(Product $selection, array $arguments = []): string
    {
        if ($selection->getTypeId() == Configurable::TYPE_CODE) {
            return '';
        }
        return parent::renderTierPrice($selection, $arguments);
    }
}
