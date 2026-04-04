<?php
namespace Bitrail\HyvaCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Invoice;

class CheckoutSuccessObserver implements ObserverInterface
{
  protected $checkoutSession;
  protected $logger;

  public function __construct(Session $checkoutSession)
  {
    $this->checkoutSession = $checkoutSession;
  }


  public function execute(Observer $observer)
  {
    $order = $observer->getEvent()->getOrder();
    $payment = $order->getPayment();
    if ($payment->getMethod() == 'bitrail') {

      $transactionCode = $this->checkoutSession->getData($order->getIncrementId());

      $amount = $order->getGrandTotal();
      $payment->setTransactionId($transactionCode)
        ->setAmount($amount)
        ->setIsTransactionClosed(true)
        ->setStatus('paid')
        ->save();

      $invoice = $order->prepareInvoice()
        ->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE)
        ->setBaseGrandTotal($amount)
        ->setGrandTotal($amount)
        ->register()
        ->pay()
        ->save();

      $order->setState(Order::STATE_PROCESSING)
        ->setStatus('processing')
        ->save();
    }
  }
}
