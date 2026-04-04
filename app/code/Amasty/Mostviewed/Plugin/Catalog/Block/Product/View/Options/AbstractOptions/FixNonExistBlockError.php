<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Catalog\Block\Product\View\Options\AbstractOptions;

use Amasty\Mostviewed\Model\Block\Renderer\Flag as RendererFlag;
use Amasty\Mostviewed\Model\Block\Renderer\GetLayout;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Framework\View\LayoutInterface;

class FixNonExistBlockError
{
    /**
     * @var GetLayout
     */
    private $getLayout;

    /**
     * @var RendererFlag
     */
    private $rendererFlag;

    public function __construct(
        GetLayout $getLayout,
        RendererFlag $rendererFlag
    ) {
        $this->getLayout = $getLayout;
        $this->rendererFlag = $rendererFlag;
    }

    /**
     * @see AbstractOptions::getLayout()
     *
     * Need replace layout for custom options for avoid
     * errors with product.price.render.default in \Magento\Catalog\Block\Product\View\Options\AbstractOptions.
     * Errors caused for select options \Magento\Catalog\Block\Product\View\Options\Type\Select,
     * because multipleFactory and checkableFactory uses shared layout from template context instead of current layout
     */
    public function afterGetLayout(AbstractOptions $abstractOptions, LayoutInterface $layout): LayoutInterface
    {
        if ($this->rendererFlag->isActive()) {
            $layout = $this->getLayout->execute($abstractOptions->getProduct()->getTypeId());
        }

        return $layout;
    }
}
