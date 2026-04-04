<?php

namespace Bitrail\HyvaCheckout\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;

class RegisterPayment extends Action
{
  protected $checkoutSession;
  protected $resultJsonFactory;

  public function __construct(
    Context $context,
    Session $checkoutSession,
    JsonFactory $resultJsonFactory,
  ) {
    parent::__construct($context);

    $this->checkoutSession = $checkoutSession;
    $this->resultJsonFactory = $resultJsonFactory;
  }

  public function execute()
  {
    $resultJson = $this->resultJsonFactory->create();
    $reqParams = $this->getRequest()->getParams();

    if (!isset($reqParams['orderId']) || !isset($reqParams['verificationToken'])) {
      return $resultJson->setData(['success' => false, 'error' => 'Required request parameters not set.']);
    }

    $orderId = $reqParams['orderId'];
    $verificationToken = $reqParams['verificationToken'];

    $this->checkoutSession->setData($orderId, $verificationToken);

    return $resultJson->setData(['success' => true, 'data' => $this->checkoutSession->getData($orderId)]);

  }
}
