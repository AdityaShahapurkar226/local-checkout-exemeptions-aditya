<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;

class MiniPage extends \Magento\Catalog\Block\Product\View
{
    public const IMAGE_TYPE = 'ammostviewed_popup_image';

    public function _construct()
    {
        $this->setTemplate('Amasty_Mostviewed::bundle/minipage.phtml');
        parent::_construct();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLoadedLayout()
    {
        return $this->getData('loaded_layout');
    }

    /**
     * @return string
     */
    public function renderPriceHtml()
    {
        $html = '';
        if ($this->getProduct()->getTypeId() !== 'giftcard') {
            $block = $this->getLayout()->getBlock('product.price.final');
            if (!$block && $this->getLoadedLayout()) {
                $block = $this->getLoadedLayout()->getBlock('product.price.final');
            }

            if ($block) {
                $html = $block->toHtml();
            }
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        return $this->getData('optionsHtml');
    }

    /**
     * @return string
     */
    public function getRatingSummary($product)
    {
        $block = $this->getLayout()->createBlock(
            \Magento\Review\Block\Product\ReviewRenderer::class,
            'amasty.mostviewed.product.review',
            [
                'data' => [
                    'product' => $product
                ]
            ]
        );

        return $block->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getImageBlock($product)
    {
        $block = $this->imageBuilder->setProduct($product)
            ->setImageId(self::IMAGE_TYPE)
            ->create();

        $html = $block->toHtml();

        return $html;
    }
}
