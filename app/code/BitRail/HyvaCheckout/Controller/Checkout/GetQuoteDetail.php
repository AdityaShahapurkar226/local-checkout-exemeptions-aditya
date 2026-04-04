<?php

namespace Bitrail\HyvaCheckout\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;
use BitRail\PaymentGateway\Gateway\Http\Client\ClientMock;
use BitRail\PaymentGateway\Gateway\Http\Client\BitrailOrderTokenizer;

class GetQuoteDetail extends Action
{
  protected $resultJsonFactory;
  protected $checkoutSession;


  public function __construct(
    Context $context,
    JsonFactory $resultJsonFactory,
    checkoutSession $checkoutSession
  ) {
    $this->resultJsonFactory = $resultJsonFactory;
    $this->checkoutSession = $checkoutSession;

    parent::__construct($context);
  }

  public function execute()
  {
    $resultJson = $this->resultJsonFactory->create();
    $reqParams = $this->getRequest()->getParams();

    if (!isset($reqParams['nonceCode']) || $reqParams['nonceCode'] !== ClientMock::getNonceCode()) {
      return $resultJson->setData(['success' => false, 'error' => 'Invalid nonce code.']);
    }
    $quote = $this->checkoutSession->getQuote();

    if (!$quote->getReservedOrderId()) {
      $quote->reserveOrderId();
      $quote->save();
    }

    $data = $this->prepareResponse($quote);
    return $resultJson->setData(['success' => true, 'data' => $data]);
  }

  protected function prepareResponse($quote)
  {
    // Prepare the response data with all necessary information
    $data = [
      'orderNumber' => $quote->getReservedOrderId(),
      'orderToken' => BitrailOrderTokenizer::getOrderToken($quote->getId()),
      'grandTotal' => number_format((float) $quote->getGrandTotal(), 2, '.', ''),
      'createdAt' => $quote->getCreatedAt(),
      'customerFirstName' => $quote->getCustomerFirstname() ?: "",
      'customerLastName' => $quote->getCustomerLastname() ?: "",
      'customerEmail' => $quote->getCustomerEmail() ?: "",
      'status' => 'pending_payment',
    ];

    // Get shipping address details
    $shippingAddress = $quote->getShippingAddress();
    $data['shippingAddress'] = $shippingAddress ? [
      'firstname' => $shippingAddress->getFirstname(),
      'lastname' => $shippingAddress->getLastname(),
      'street' => implode(', ', $shippingAddress->getStreet()),
      'city' => $shippingAddress->getCity(),
      'region' => $shippingAddress->getRegion(),
      'postcode' => $shippingAddress->getPostcode(),
      'countryId' => $shippingAddress->getCountryId(),
      'telephone' => $shippingAddress->getTelephone(),
    ] : null;

    // Get billing address details
    $billingAddress = $quote->getBillingAddress();
    $data['billingAddress'] = $billingAddress ? [
      'firstname' => $billingAddress->getFirstname(),
      'lastname' => $billingAddress->getLastname(),
      'street' => implode(', ', $billingAddress->getStreet()),
      'city' => $billingAddress->getCity(),
      'region' => $billingAddress->getRegion(),
      'postcode' => $billingAddress->getPostcode(),
      'countryId' => $billingAddress->getCountryId(),
      'telephone' => $billingAddress->getTelephone(),
    ] : null;

    return $data;
  }
}
