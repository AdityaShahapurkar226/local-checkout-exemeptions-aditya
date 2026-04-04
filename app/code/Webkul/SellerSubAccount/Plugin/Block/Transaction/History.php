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
namespace Webkul\SellerSubAccount\Plugin\Block\Transaction;

use Webkul\SellerSubAccount\Helper\Data as HelperData;

class History
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
     * @param \Webkul\Marketplace\Block\Transaction\History $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundGetCustomerId(
        \Webkul\Marketplace\Block\Transaction\History $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }
        return $this->_helper->getSubAccountSellerId();
    }
}
