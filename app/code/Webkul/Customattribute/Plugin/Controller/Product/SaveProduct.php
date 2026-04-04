<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Plugin\Controller\Product;

class SaveProduct
{
    /**
     * Before save seller product data
     *
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $subject
     * @param int $sellerId
     * @param array $wholedata
     * @return array
     */
    public function beforeSaveProductData(
        \Webkul\Marketplace\Controller\Product\SaveProduct $subject,
        $sellerId,
        $wholedata
    ) {
       
        if (isset($wholedata['product']) && !isset($wholedata['product']['tier_price'])) {
            $wholedata['product']['tier_price'] = [];
        }
        return [$sellerId, $wholedata];
    }
}
