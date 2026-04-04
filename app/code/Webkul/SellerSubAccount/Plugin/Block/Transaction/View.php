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
use Webkul\Marketplace\Model\Saleslist;

class View
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var Saleslist
     */
    public $_saleslist;

    /**
     * @param HelperData $helper
     * @param Saleslist  $saleslist
     */
    public function __construct(
        HelperData $helper,
        Saleslist $saleslist
    ) {
        $this->_helper = $helper;
        $this->_saleslist = $saleslist;
    }

    /**
     * Around Seller Transaction Order Details.
     *
     * @param \Webkul\Marketplace\Block\Transaction\View $block
     * @param \Closure $proceed
     * @param int $id
     *
     * @return \Webkul\Marketplace\Model\Saleslist
     */
    public function aroundSellertransactionOrderDetails(
        \Webkul\Marketplace\Block\Transaction\View $block,
        \Closure $proceed,
        $id
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed($id);
        }
        $sellerId = $this->_helper->getSubAccountSellerId();

        return $this->_saleslist
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        )
        ->addFieldToFilter(
            'trans_id',
            ['eq' => $id]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        );
    }
}
