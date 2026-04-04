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
namespace Webkul\SellerSubAccount\Plugin\Block\Order;

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
     * @param \Webkul\Marketplace\Block\Order\History $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundGetCustomerId(
        \Webkul\Marketplace\Block\Order\History $block,
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
