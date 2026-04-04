<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Controller\Order\Shipment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

/**
 * Class Save used for saving shipment
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     */
    private $shipmentValidator;

    /**
     * @param Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     */
    public function __construct(
        \Webkul\MarketplaceBaseShipping\Helper\Data $baseShippingHelper,
        Context $context,
        \Webkul\Marketplace\Model\OrdersFactory $marketplaceOrderData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Webkul\MarketplaceBaseShipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
    ) {
        $this->baseShippingHelper = $baseShippingHelper;
        $this->shipmentLoader = $shipmentLoader;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
        $this->marketplaceOrderData = $marketplaceOrderData;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipmentData = $this->_customerSession->getData('shipment_data');
        //
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        $shipmentId = $shipment->getId();

        $sellerCollection = $this->marketplaceOrderData->create()
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $shipment->getOrder()->getId()]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->_customerSession->getCustomerId()]
        );
        
        foreach ($sellerCollection as $row) {
            if ($shipment->getId() != '') {
                $row->setShipmentId($shipment->getId());
                $row->setTrackingNumber($shipmentData['tracking_number']);
                $row->save();
            }
        }
        return $this;
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $displayErrors=$this->baseShippingHelper->displayErrors();

        $isPost = $this->getRequest()->isPost();
        if (!$isPost) {
            $this->messageManager->addError(__('We can\'t save the shipment right now.'));
            return $resultRedirect->setPath(
                'marketplace/order/view',
                ['id' => $this->getRequest()->getParam('order_id')]
            );
        }

        $data = $this->getRequest()->getParam('shipment');

        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

        $responseAjax = new \Magento\Framework\DataObject();

        try {
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($data);
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                $this->_forward('noroute');
                return;
            }

            $validationResult = $this->getShipmentValidator()
                ->validate($shipment, [QuantityValidator::class]);

            if ($validationResult->hasMessages()) {
                $this->messageManager->addError(
                    __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                );
                $this->_redirect('marketplace/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
                return;
            }
            $shipment->register();

            if ($isNeedCreateLabel) {
                $this->labelGenerator->create($shipment, $this->_request);
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            if (!empty($data['send_email'])) {
                $this->shipmentSender->send($shipment);
            }

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage = __('You created the shipping label.');

            $this->messageManager->addSuccess(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
            //$this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('marketplace/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
        } catch (\Exception $e) {
            $displayErrors->critical($e->getMessage());
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(__('An error occurred while creating shipping label.'));
            } else {
                $this->messageManager->addError(__('Cannot save shipment.'));
                $this->_redirect('marketplace/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
            }
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->representJson($responseAjax->toJson());
        } else {
            $this->_redirect('marketplace/order/view', ['order_id' => $shipment->getOrderId()]);
        }
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     * @deprecated 100.1.1
     */
    private function getShipmentValidator()
    {
        if ($this->shipmentValidator === null) {
            $this->shipmentValidator = $this->_objectManager->get(
                \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class
            );
        }

        return $this->shipmentValidator;
    }
}
