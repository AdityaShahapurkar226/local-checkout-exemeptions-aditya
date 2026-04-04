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
namespace Webkul\SellerSubAccount\Plugin\Controller\Order\Creditmemo;

use Webkul\SellerSubAccount\Helper\Data as HelperData;

class Create
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
     * After Get Customer Id
     *
     * @param \Webkul\Marketplace\Controller\Order\Creditmemo\Create $block
     * @param int $sellerId
     *
     * @return int $sellerId
     */
    public function afterGetCustomerId(
        \Webkul\Marketplace\Controller\Order\Creditmemo\Create $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $sellerId;
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return $sellerId;
    }
}
