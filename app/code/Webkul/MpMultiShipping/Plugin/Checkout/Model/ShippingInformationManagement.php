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
namespace Webkul\MpMultiShipping\Plugin\Checkout\Model;

use Magento\Framework\Session\SessionManager;

class ShippingInformationManagement
{
    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $_coreSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Webkul\MpMultiShipping\Logger\Logger
     */
    protected $logger;

    /**
     * @param SessionManager $coreSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Webkul\MpMultiShipping\Logger\Logger $logger
     */
    public function __construct(
        SessionManager $coreSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Webkul\MpMultiShipping\Logger\Logger $logger
    ) {
        $this->_coreSession = $coreSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        try {
            $extAttributes = $addressInformation->getExtensionAttributes();
            $selectedShipping = $extAttributes->getSelectedShipping();
            $multiCustomship = $extAttributes->getMultiCustomship();
            $this->_coreSession->setSelectedAmount($multiCustomship);
            $this->_coreSession->setSelectedMethods($this->jsonSerializer->unserialize($selectedShipping));
        } catch (\Exception $e) {
            $this->logger->addError('beforeSaveAddressInformation : '.$e->getMessage());
        }
    }
}
