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

namespace Webkul\MarketplaceBaseShipping\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class Shipping
{
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $session,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * before execute
     * @param Magento\Quote\Model\Quote\Address\Total\Shipping $subject
     * @return null
     */
    public function beforeCollect(
        \Magento\Quote\Model\Quote\Address\Total\Shipping $subject,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        try {
            if (!count($shippingAssignment->getItems())) {
                return [$quote, $shippingAssignment, $total];
            }

            $val = (int) $this->session->getAddressSequence();
            $this->session->setAddressSequence($val+1);
        } catch (\Exception $ex) {
            $this->logger->info($ex->getMessage());
        }
    }
}
