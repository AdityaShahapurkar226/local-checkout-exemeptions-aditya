<?php
/**
 * Webkul Software Pvt. Ltd.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceBaseShipping\Plugin\Multishipping\Controller\Checkout;

use Magento\Customer\Model\Session as CustomerSession;

class CheckItems
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Constructor
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * before execute
     * @param Magento\Multishipping\Controller\Checkout\CheckItems $subject
     * @return null
     */
    public function beforeExecute(\Magento\Multishipping\Controller\Checkout\CheckItems $subject)
    {
        $this->customerSession->setShippingInformation(null);
    }
}
