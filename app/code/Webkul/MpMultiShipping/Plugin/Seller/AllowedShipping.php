<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\Plugin\Seller;

use Magento\Framework\Session\SessionManager;

class AllowedShipping
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param SessionManager $coreSession
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        SessionManager $coreSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->_coreSession = $coreSession;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $result
     */
    public function afterIsShippineAvlForSeller(
        \Webkul\Marketplace\Block\Account\Navigation\ShippingMenu $subject,
        $result
    ) {
        return $result;
    }
}
