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
namespace Webkul\SellerSubAccount\Ui\DataProvider\Product;

/**
 * Class UpSellDataProvider up sell data provider
 */
class UpSellDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc
     */
    public function getLinkType()
    {
        return 'up_sell';
    }
}
