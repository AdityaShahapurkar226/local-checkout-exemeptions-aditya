<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MarketplaceBaseShipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Controller\Shipment;

use Magento\Framework\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as Directory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;

class PrintLabel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Filesystem\Directory
     */
    protected $_directoryList;
    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Action\Context                  $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param DirectoryList                   $directoryList
     * @param LabelGenerator                  $labelGenerator
     * @param FileFactory                     $fileFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        DirectoryList $directoryList,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory
    ) {
        $this->_directoryList = $directoryList;
        $this->_customerSession = $customerSession;
        $this->_fileFactory = $fileFactory;
        $this->_labelGenerator = $labelGenerator;
        parent::__construct($context);
    }

    /**
     * return label in pdf formate.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $customerId = $this->_customerSession->getCustomerId();
        $marketplaceOrder = $this->_objectManager->create(\Webkul\Marketplace\Model\Orders::class)
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id', ['eq' => $customerId])
                                    ->addFieldToFilter('order_id', ['eq' => $orderId]);
        $trackingNumber = '';
        $labelsContent = [];
        foreach ($marketplaceOrder as $order) {
            $trackingNumber = $order->getTrackingNumber();
            $labelsContent[] = $order->getShipmentLabel();
        }
        
        if ($trackingNumber != '') {
            if (!empty($labelsContent)) {
                $outputPdf = $this->_labelGenerator->combineLabelsPdf($labelsContent);
                return $this->_fileFactory->create(
                    'ShippingLabels.pdf',
                    $outputPdf->render(),
                    Directory::VAR_DIR,
                    'application/pdf'
                );
            }
        }
    }
}
