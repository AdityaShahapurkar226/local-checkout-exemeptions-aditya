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
namespace Webkul\SellerSubAccount\Plugin\Block\Account\Dashboard;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\Saleslist;

class Diagrams
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var Order
     */
    public $_order;

    /**
     * @var Saleslist
     */
    public $_saleslist;

    /**
     * @param HelperData          $helper
     * @param MarketplaceHelper   $marketplaceHelper
     * @param Order               $order
     * @param Saleslist           $saleslist
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        Order $order,
        Saleslist $saleslist
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_order = $order;
        $this->_saleslist = $saleslist;
    }

    /**
     * Before Get Yearly Sale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param int $sellerId
     *
     * @return array
     */
    public function beforeGetYearlySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * Before Get Monthly Sale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param int $sellerId
     *
     * @return array
     */
    public function beforeGetMonthlySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * Before Get Weekly Sale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param int $sellerId
     *
     * @return array
     */
    public function beforeGetWeeklySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * Before Get Daily Sale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param int $sellerId
     *
     * @return array
     */
    public function beforeGetDailySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }
}
