<?php

namespace Ahy\ThemeCustomization\Plugin;

use Magento\Catalog\Model\Product;

class ImageLoggerPlugin
{
    public function beforeCreate(
        \Magento\Catalog\Block\Product\ImageFactory $subject,
        Product $product,
        string $imageId,
        array $attributes = null
    ) {

        \Magento\FrameWork\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug("Pravin ImageFactory::create() called with imageId = $imageId, product ID = {$product->getId()}");

        return [$product, $imageId, $attributes];
    }
}
