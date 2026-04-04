<?php

namespace Ahy\Authorizenet\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Ahy\Authorizenet\Service\AuthorizeNetApi;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magewirephp\Magewire\Component;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Ahy\Authorizenet\Helper\Data;
use Ahy\Authorizenet\Logger\Logger as AuthorizeLogger;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Ahy\Authorizenet\Service\CustomerProfileCreator;

class PlaceOrderService extends AbstractPlaceOrderService {
    protected $_authorizeNetApi;
    protected $checkoutSession;
    protected $canPlaceOrder    = true;
    protected $_helperData;
    protected $authorizeLogger;
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;
    protected OrderRepositoryInterface $orderRepository;
    protected CustomerProfileCreator $customerProfileCreator;

    const LOGGER_ENABLED = true;

    public function __construct(
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        Session                 $checkoutSession,
        Data                    $helperData,
        AuthorizeLogger         $authorizeLogger,
        AuthorizeNetApi         $authorizeNetApi,
        InvoiceService          $invoiceService,
        InvoiceSender           $invoiceSender,
        Transaction             $transaction,
        CustomerProfileCreator $customerProfileCreator
    ) {
        parent::__construct($cartManagement);
        $this->_authorizeNetApi = $authorizeNetApi;
        $this->checkoutSession  = $checkoutSession;
        $this->orderRepository  = $orderRepository;
        $this->authorizeLogger  = $authorizeLogger;
        $this->_helperData      = $helperData;
        $this->invoiceService   = $invoiceService;
        $this->transaction      = $transaction;
        $this->invoiceSender    = $invoiceSender;
        $this->customerProfileCreator = $customerProfileCreator;
    }

    public function placeOrder(Quote $quote): int 
    {
        try {
            $encryptionKey  = $this->_helperData->getEncryptionKey();
            $cardNumber     = $this->_decrypt($this->checkoutSession->getData('B4yPd7T5mWuR'),      $encryptionKey);
            $expireMonth    = $this->_decrypt($this->checkoutSession->getData('V3g6dC4hQ9m8X2L5'),  $encryptionKey);
            $expireYear     = $this->_decrypt($this->checkoutSession->getData('L8wKSe1cUVm'),       $encryptionKey);
            $cardCvv        = $this->_decrypt($this->checkoutSession->getData('kzgD3B7n'),          $encryptionKey);
            $totalAmount    = $quote->getGrandTotal();

            $billingAddress = $this->checkoutSession->getQuote()->getBillingAddress();
            $billingAddressArray = [];
            if ($billingAddress) {
                $countryCode    = $billingAddress->getData('country_id');
                $firstName      = $billingAddress->getData('firstname');
                $telephone      = $billingAddress->getData('telephone');
                $postCode       = $billingAddress->getData('postcode');
                $lastName       = $billingAddress->getData('lastname');
                $street         = $billingAddress->getData('street');
                $region         = $billingAddress->getData('region');
                $city           = $billingAddress->getData('city');

                $billingAddressArray = [
                    'country_code'  => $countryCode,
                    'firstName'     => $firstName,
                    'telephone'     => $telephone,
                    'lastName'      => $lastName,
                    'postcode'      => $postCode,
                    'street'        => $street,
                    'region'        => $region,
                    'city'          => $city
                ];
            }

            $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
            $shippingAddressArray = [];

            if ($shippingAddress) {
                $countryCode    = $shippingAddress->getData('country_id');
                $firstName      = $shippingAddress->getData('firstname');
                $telephone      = $shippingAddress->getData('telephone');
                $postCode       = $shippingAddress->getData('postcode');
                $lastName       = $shippingAddress->getData('lastname');
                $street         = $shippingAddress->getData('street');
                $region         = $shippingAddress->getData('region');
                $city           = $shippingAddress->getData('city');

                $shippingAddressArray = [
                    'country_code'  => $countryCode,
                    'firstName'     => $firstName,
                    'telephone'     => $telephone,
                    'lastName'      => $lastName,
                    'postcode'      => $postCode,
                    'street'        => $street,
                    'region'        => $region,
                    'city'          => $city
                ];
            }
            $customerDetailsArray = [];
            $quote = $this->checkoutSession->getQuote();
            if ($quote && $quote->getCustomer()) {
                $customerId = $quote->getCustomer()->getId();
                $email      = $billingAddress->getData('email');
                $customerDetailsArray = [
                    'customerId'    => $customerId,
                    'email'         => $email
                ];
            } else {
                $email = $billingAddress->getData('email');
                $customerDetailsArray = [
                    'email' => $email
                ];
            }

            $cardNumber     = $this->_validateCardDetails($cardNumber, $expireMonth, $expireYear, $cardCvv);
            $responseJson   = $this->createCharge($cardNumber, $expireMonth, $expireYear, $cardCvv, $totalAmount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray);
            $responseArray  = json_decode($responseJson);
            if(self::LOGGER_ENABLED) {
                $this->authorizeLogger->info('"' . json_encode($responseArray) . '"' . ' from Ahy\Authorizenet\Model\Magewire\Payment\PlaceOrderService');
            }
            if ($responseArray === null) {
                $errorCode      = json_last_error();
                $errorMessage   = json_last_error_msg();
                // Handle the error appropriately
                $authorizeNetApiResponseArrayError = $errorMessage;
                throw new Exception($authorizeNetApiResponseArrayError);
            }

            $authorizeNetApiResponseCode            = $responseArray->messages->resultCode;
            $authorizeNetApiAvsResultCode           = $responseArray->transactionResponse->avsResultCode;
            $authorizeNetApiTransactionResponseCode = $responseArray->transactionResponse->responseCode;

            if (isset($responseArray->transactionResponse->errors) && count($responseArray->transactionResponse->errors) > 0) {
                $authorizeNetApiResponseMessage = $responseArray->transactionResponse->errors[0]->errorText;
            } else {
                $authorizeNetApiResponseMessage = $responseArray->messages->message[0]->text;
            }

            // if ($authorizeNetApiResponseCode !== 'Ok' || ($authorizeNetApiTransactionResponseCode !== '1' && $authorizeNetApiTransactionResponseCode !== '4')) {
            //     // If the response code is not Ok or the transaction response code is not 1, throw an exception
            //     if(self::LOGGER_ENABLED) {
            //         $this->authorizeLogger->info('"' . $authorizeNetApiResponseMessage . '"' . ' from Ahy\Authorizenet\Model\Magewire\Payment\PlaceOrderService');
            //     }
            //     throw new Exception($authorizeNetApiResponseMessage);
            // }

            $cardNumber     = $responseArray->transactionResponse->accountNumber;
            $cardType       = $responseArray->transactionResponse->accountType;
            $transId        = $responseArray->transactionResponse->transId;
            $refTransId     = $responseArray->transactionResponse->refTransID;
            $avsResponse    = $responseArray->transactionResponse->avsResultCode;
            $cvvResponse    = $responseArray->transactionResponse->cvvResultCode;

            $payment = $quote->getPayment();
            if ($payment) {
                $payment->setAdditionalInformation('card_number', $cardNumber);
                $payment->setAdditionalInformation('cvv_response', $cvvResponse);
                $payment->setAdditionalInformation('card_type', $cardType);
                $payment->setAdditionalInformation('expiry', $expireMonth . '/' . $expireYear);
                $payment->setAdditionalInformation('trans_id', $transId);
                $payment->setAdditionalInformation('ref_trans_id', $refTransId);
                $payment->setAdditionalInformation('avs_response', $avsResponse);
                $payment->save();
            }

            $isSameAddress = (
                $shippingAddressArray['city'] == $billingAddressArray['city'] &&
                $shippingAddressArray['region'] == $billingAddressArray['region'] &&
                $shippingAddressArray['postcode'] == $billingAddressArray['postcode'] &&
                $shippingAddressArray['street'] == $billingAddressArray['street']
            );

            if (!$isSameAddress) {
                $orderId = parent::placeOrder($quote);
                // Load the order by ID
                $order = $this->orderRepository->get($orderId);

                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(Order::STATUS_FRAUD);
            } else{
                // $authorizeNetApiAvsResultCode is already defined and has a value
                // switch ($authorizeNetApiAvsResultCode) {
                //     case 'Y':
                //     case 'U':
                //     case 'S':
                //     case 'B':
                //     case 'A':
                //     case 'Z':
                //     case 'W':
                        $orderId = parent::placeOrder($quote);
                        // Load the order by ID
                        $order   = $this->orderRepository->get($orderId);

                        // Set the order state and status to processing
                        $order->setState(Order::STATE_PROCESSING)
                            ->setStatus(Order::STATE_PROCESSING);

                        // Unset the data from the session
                        $this->checkoutSession->unsetData('B4yPd7T5mWuR');
                        $this->checkoutSession->unsetData('V3g6dC4hQ9m8X2L5');
                        $this->checkoutSession->unsetData('L8wKSe1cUVm');
                        $this->checkoutSession->unsetData('kzgD3B7n');

                //         break;
                //     case 'P':
                //         // Set the order state and status to processing
                //         throw new Exception("Oops! It seems there was an issue processing your payment. The card details provided may not be valid. Please try again with a different card or contact your bank for assistance.");
                //     default:
                //         $orderId = parent::placeOrder($quote);
                //         // Load the order by ID
                //         $order   = $this->orderRepository->get($orderId);

                //         // Set the order state and status to suspected fraud
                //         $order->setState(Order::STATE_PROCESSING)
                //             ->setStatus(Order::STATUS_FRAUD);

                //             // Unset the data from the session
                //             $this->checkoutSession->unsetData('B4yPd7T5mWuR');
                //             $this->checkoutSession->unsetData('V3g6dC4hQ9m8X2L5');
                //             $this->checkoutSession->unsetData('L8wKSe1cUVm');
                //             $this->checkoutSession->unsetData('kzgD3B7n');

                //         break;
                //  }
            }

            // Save the order
            $this->orderRepository->save($order);
            if (!empty($transId)) {
                $last4 = substr(preg_replace('/\D/', '', $cardNumber), -4);
                $this->customerProfileCreator->createFromTransaction(
                    $transId,
                    $order->getCustomerEmail(),
                    (string) $order->getCustomerId(),  // as merchantCustomerId
                    (int) $order->getCustomerId(),     // actual customerId
                    $last4,
                    $expireMonth,
                    $expireYear,
                    $cardType
                );
            }

            // Check if the order can be invoiced
            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->save();
                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //Send Invoice mail to customer
                $order->addStatusHistoryComment(__('Notified customer about invoice creation #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
            }
            return $orderId;
        } catch (Exception $e) {
            // Handle the exception or log the error message
            $errorMessage = $e->getMessage();
            // Return an appropriate error code or message to indicate the failure
            if(self::LOGGER_ENABLED) {
                $this->authorizeLogger->error( ' In catch ' . ' " ' . $errorMessage . ' " ' . ' from Ahy\Authorizenet\Model\Magewire\Payment\PlaceOrderService');
            }
            throw new Exception($errorMessage);
        }
    }

    private function _decrypt($encryptedString, $key)
    {
        if(NULL !== $encryptedString){

            $method             = 'aes-256-cbc';
            $key                = substr(hash('sha256', $key), 0, 32);
            $data               = base64_decode($encryptedString);
            $iv                 = substr($data, 0, 16);
            $encryptedData      = substr($data, 16);
            $decryptedString    = openssl_decrypt($encryptedData, $method, $key, OPENSSL_RAW_DATA, $iv);
            return $decryptedString;
        }
    }

    private function _validateCardDetails($cardNumber, $expireMonth, $expireYear, $cardCvv){
         // Validate card number (only digits and remove whitespace)
        $cardNumber = preg_replace('/\s+/', '', $cardNumber ?? '');
        if (!preg_match('/^\d{12,16}$/', $cardNumber)) {
            // Card number contains non-digit characters
            $authorizeNetCardValidationError = "Invalid Card Number";
            throw new Exception($authorizeNetCardValidationError);
        }
        
        // Validate expire year (must be current year or within the next 20 years)
        $currentYear    = date('Y');
        $expireYear     = (int) $expireYear;
        $isYearValid    = ($expireYear >= $currentYear && $expireYear <= $currentYear + 20);

        // Validate expire month (must be between 1 and 12)
        $expireMonth    = (int) $expireMonth;
        $isMonthValid   = ($expireMonth >= 1 && $expireMonth <= 12);

        // Validate card expiry date
        if (!$isYearValid || !$isMonthValid || ($expireYear == $currentYear && $expireMonth < date('n'))) {
            $authorizeNetCardValidationError = "Invalid Expiry";
            throw new Exception($authorizeNetCardValidationError);
        }
        if ($cardCvv === null || !ctype_digit($cardCvv) || strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
            // Invalid card CVV
            $authorizeNetCardValidationError = "Invalid CVV ";
            throw new Exception($authorizeNetCardValidationError);
        }

        
        return $cardNumber;
    }

    public function createCharge($cardNumber, $expireMonth, $expireYear, $cardCvv, $totalAmount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray)
    {   
        $isSameAddress = (
            $shippingAddressArray['city'] == $billingAddressArray['city'] &&
            $shippingAddressArray['region'] == $billingAddressArray['region'] &&
            $shippingAddressArray['postcode'] == $billingAddressArray['postcode'] &&
            $shippingAddressArray['street'] == $billingAddressArray['street']
        );

        if (!$isSameAddress) {
            return $this->_authorizeNetApi->authorizeCard($cardNumber, $expireMonth, $expireYear, $cardCvv, $totalAmount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray);
        } else{
            return $this->_authorizeNetApi->createCharge($cardNumber, $expireMonth, $expireYear, $cardCvv, $totalAmount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray);
        }
    }
}