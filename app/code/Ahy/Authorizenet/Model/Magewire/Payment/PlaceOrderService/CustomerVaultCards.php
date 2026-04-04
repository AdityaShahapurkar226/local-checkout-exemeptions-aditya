<?php

namespace Ahy\Authorizenet\Model\Magewire\Payment\PlaceOrderService;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Ahy\Authorizenet\Service\AuthorizeNetApi;
use Ahy\Authorizenet\Model\CustomerProfileRepository;
use Magento\Sales\Model\Order;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Exception;

class CustomerVaultCards extends AbstractPlaceOrderService
{
    protected LoggerInterface $logger;
    protected OrderRepositoryInterface $orderRepository;
    protected PaymentInformationManagement $paymentInformationManagement;
    protected PaymentTokenRepositoryInterface $vaultTokenRepository;
    protected CustomerProfileRepository $customerProfileRepository;
    protected AuthorizeNetApi $authorizeNetApi;
    protected InvoiceService $invoiceService;
    protected Transaction $transaction;
    protected InvoiceSender $invoiceSender;
    protected PaymentTokenCollectionFactory $paymentTokenCollectionFactory;
    protected CartManagementInterface $cartManagement;
    protected SessionManagerInterface $session;

    public function __construct(
        LoggerInterface                 $logger,
        OrderRepositoryInterface        $orderRepository,
        PaymentInformationManagement    $paymentInformationManagement,
        PaymentTokenRepositoryInterface $vaultTokenRepository,
        CustomerProfileRepository       $customerProfileRepository,
        AuthorizeNetApi                 $authorizeNetApi,
        InvoiceService                  $invoiceService,
        Transaction                     $transaction,
        InvoiceSender                   $invoiceSender,
        PaymentTokenCollectionFactory   $paymentTokenCollectionFactory,
        CartManagementInterface         $cartManagement,
        SessionManagerInterface         $session
    ) {
        parent::__construct($cartManagement);
        $this->logger                        = $logger;
        $this->orderRepository               = $orderRepository;
        $this->paymentInformationManagement  = $paymentInformationManagement;
        $this->vaultTokenRepository          = $vaultTokenRepository;
        $this->customerProfileRepository     = $customerProfileRepository;
        $this->authorizeNetApi               = $authorizeNetApi;
        $this->invoiceService                = $invoiceService;
        $this->transaction                   = $transaction;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->invoiceSender                 = $invoiceSender;
        $this->session                       = $session;
    }

    public function placeOrder(Quote $quote): int
    {
        $this->logger->info('CustomerVaultCards: Starting placeOrder using saved card');

        $cardData = $this->session->getData('authnet_selected_card');

        $publicHash        = $cardData['public_hash'] ?? null;
        $customerProfileId = $cardData['customer_profile_id'] ?? null;
        $paymentProfileId  = $cardData['customer_payment_profile_id'] ?? null;

        $this->logger->info("Vault Hash " . ($publicHash ?: 'N/A'));
        $this->logger->info("CustomerProfileId " . ($customerProfileId ?: 'N/A'));
        $this->logger->info("PaymentProfileId " . ($paymentProfileId ?: 'N/A'));

        if (!$publicHash || !$customerProfileId || !$paymentProfileId) {
            throw new Exception('Missing required token data from session.');
        }

        $amount         = $quote->getGrandTotal();
        $customerId     = (int) $quote->getCustomer()->getId();
        $customerEmail  = $quote->getCustomer()->getEmail();

        $billing = $quote->getBillingAddress();
        $billingArray = [
            'firstName'    => $billing->getFirstname(),
            'lastName'     => $billing->getLastname(),
            'street'       => implode(' ', (array) $billing->getStreet()),
            'city'         => $billing->getCity(),
            'region'       => $billing->getRegion(),
            'postcode'     => $billing->getPostcode(),
            'country_code' => $billing->getCountryId(),
            'telephone'    => $billing->getTelephone(),
        ];

        $shipping = $quote->getShippingAddress();
        $shippingArray = [
            'firstName'    => $shipping->getFirstname(),
            'lastName'     => $shipping->getLastname(),
            'street'       => implode(' ', (array) $shipping->getStreet()),
            'city'         => $shipping->getCity(),
            'region'       => $shipping->getRegion(),
            'postcode'     => $shipping->getPostcode(),
            'country_code' => $shipping->getCountryId(),
            'telephone'    => $shipping->getTelephone(),
        ];

        $customerDetails = [
            'customerId' => (string) $customerId,
            'email'      => $customerEmail,
        ];

        $responseJson = $this->authorizeNetApi->chargeSavedCard(
            $customerProfileId,
            $paymentProfileId,
            $amount,
            $shippingArray,
            $billingArray,
            $customerDetails
        );

        $response = json_decode($responseJson);

        if (!$response || $response->messages->resultCode !== 'Ok') {
            $errorMsg = $response->messages->message[0]->text ?? 'Authorize.Net transaction failed.';
            throw new Exception($errorMsg);
        }

        $transactionId = $response->transactionResponse->transId       ?? null;
        $cardType      = $response->transactionResponse->accountType   ?? null;
        $maskedCard    = $response->transactionResponse->accountNumber ?? null;

        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('trans_id', $transactionId);
        $payment->setAdditionalInformation('card_type', $cardType);
        $payment->setAdditionalInformation('masked_card', $maskedCard);
        $payment->save();

        $orderId = parent::placeOrder($quote);
        $order   = $this->orderRepository->get($orderId);

        $order->setState(Order::STATE_PROCESSING)
              ->setStatus(Order::STATE_PROCESSING);
        $this->orderRepository->save($order);

        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register()->save();

            $this->transaction->addObject($invoice)->addObject($invoice->getOrder())->save();
            $this->invoiceSender->send($invoice);

            $order->addStatusHistoryComment(__('Customer notified about invoice creation #%1.', $invoice->getId()))
                  ->setIsCustomerNotified(true)
                  ->save();
        }

        $this->session->unsetData('authnet_selected_card');

        return $orderId;
    }

    public function customerHasSavedCards(): bool
    {
        try {
            $customerId = $this->session->getCustomerId();

            if (!$customerId) {
                return false;
            }

            $collection = $this->paymentTokenCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_active', 1);

            return (bool) $collection->getSize();
        } catch (\Exception $e) {
            $this->logger->error('[Vault] Error checking saved cards: ' . $e->getMessage());
            return false;
        }
    }
    public function canPlaceOrder(): bool
    {
        return true;
    }
}
