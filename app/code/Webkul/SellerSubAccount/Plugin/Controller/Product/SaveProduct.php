<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Plugin\Controller\Product;

use Webkul\SellerSubAccount\Helper\Data as HelperData;

class SaveProduct
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @param HelperData        $helper
     */
    public function __construct(
        HelperData $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Before Save Product Data
     *
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $block
     * @param int $sellerId
     * @param array $wholedata
     *
     * @return array
     */
    public function beforeSaveProductData(
        \Webkul\Marketplace\Controller\Product\SaveProduct $block,
        $sellerId,
        $wholedata
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId, $wholedata];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId, $wholedata];
    }
}
