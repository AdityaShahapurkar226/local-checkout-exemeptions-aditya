<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Quote extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Crosssell $block
     * @return array
     */
    public function getCartProductIds($block)
    {
        $ids = [];
        $itemsCollection = $block->getQuote()->getItemsCollection();
        foreach ($itemsCollection as $item) {
            $product = $item->getProduct();
            if ($product) {
                $ids[] = $product->getId();
            }
        }

        return $ids;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Crosssell|null $block
     * @return null|\Magento\Catalog\Model\Product
     */
    public function getLastAddedProductInCart($block = null)
    {
        $items = $block ? $block->getQuote()->getAllVisibleItems() : $this->session->getQuote()->getAllVisibleItems();
        if (!empty($items)) {
            $result = array_reverse($items);
            $product = array_key_exists(0, $result) ? $result[0]->getProduct() : null;
        } else {
            $product = null;
        }

        return $product;
    }
}
