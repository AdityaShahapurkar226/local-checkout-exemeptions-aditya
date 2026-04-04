<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Analytics;

use Amasty\Mostviewed\Block\Widget\Related as AmastyRelated;
use Magento\Catalog\Block\Product\ProductList\Related;
use Magento\Catalog\Block\Product\ProductList\Upsell;
use Magento\Checkout\Block\Cart\Crosssell;

class Ctr
{
    /**
     * @param AmastyRelated|Related|Crosssell|Upsell $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        $groupId = $subject->getGroupId();
        if ($groupId && trim($result)) {
            $result .= $subject->getLayout()->createBlock(
                \Amasty\Mostviewed\Block\Analytics\Viewed::class,
                '',
                [
                    'data' => [
                        'block_id'        => $groupId,
                        'products_filter' => $subject->getMostviewedProducts(),
                        'block_type'      => $subject->getType()
                    ]
                ]
            )->toHtml();
        }

        return $result;
    }
}
