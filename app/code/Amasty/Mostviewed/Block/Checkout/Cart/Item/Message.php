<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Checkout\Cart\Item;

use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item;

class Message extends Template
{
    /**
     * @var Item
     */
    private $item;

    public function setItem(Item $item): Message
    {
        $this->item = $item;
        return $this;
    }

    public function isItemInBundlePack(): bool
    {
        return $this->item->getOptionByCode(AddProductsByIds::BUNDLE_PACK_OPTION_CODE) !== null;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->isItemInBundlePack() ? parent::_toHtml() : '';
    }
}
