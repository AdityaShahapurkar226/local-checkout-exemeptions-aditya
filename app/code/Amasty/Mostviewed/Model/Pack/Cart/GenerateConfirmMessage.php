<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class GenerateConfirmMessage
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Template|null
     */
    private $block;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    public function execute(array $products): string
    {
        $block = $this->getBlock();
        $block->setData('products', $products);

        return $block->toHtml();
    }

    private function getBlock(): Template
    {
        if ($this->block === null) {
            $this->layout->getUpdate()->addHandle('ammostviewed_confirm');
            $this->block = $this->layout->getBlock('amasty.mostviewed.confirm');
        }

        return $this->block;
    }
}
