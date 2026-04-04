<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component\Listing\Columns;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if (isset($item[$this->getData('name')]) && !is_array($item[$this->getData('name')])) {
            $item[$this->getData('name')] = explode(',', $item[$this->getData('name')]);
        }

        return parent::prepareItem($item);
    }
}
