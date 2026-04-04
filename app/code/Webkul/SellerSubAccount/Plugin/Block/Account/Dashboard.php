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
namespace Webkul\SellerSubAccount\Plugin\Block\Account;

use Webkul\SellerSubAccount\Helper\Data as HelperData;

class Dashboard
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @param HelperData $helper
     */
    public function __construct(
        HelperData $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Get Customer Id.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundGetCustomerId(
        \Webkul\Marketplace\Block\Account\Dashboard $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }

        return $this->_helper->getSubAccountSellerId();
    }
}
