<?php

namespace Ahy\Authorizenet\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session;
use Ahy\Authorizenet\Service\AuthorizeNetApi;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Ahy\Authorizenet\Helper\Data;
use Psr\Log\LoggerInterface;

class AuthorizeNet extends Component implements EvaluationInterface
{
    protected $eventManager;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $paymentInfo;
    protected $_authorizeNetApi;
    protected $_grandTotal;
    protected $_helperData;
    protected $quote;
    protected $logger;
    
    public $cardDetails = 'NA';
    public $cardNumber = null;
    public $expireMonth = null;
    public $expireYear = null;
    public $cardCvv = null;
    public $checkboxChecked = false;

    public function __construct(
        AuthorizeNetApi             $authorizeNetApi,
        Session                     $checkoutSession,
        CartRepositoryInterface     $quoteRepository,
        CartInterface               $cartInterface,
        Data                        $helperData,
        EventManager                $eventManager,
        LoggerInterface             $logger
    ) {
        $this->_authorizeNetApi     = $authorizeNetApi;
        $this->eventManager         = $eventManager;
        $this->checkoutSession      = $checkoutSession;
        $this->quoteRepository      = $quoteRepository;
        $this->quote                = $cartInterface;
        $this->_helperData          = $helperData;
        $this->logger               = $logger;
    }

    // Track checkbox updates and store immediately
    public function updatedCheckboxChecked($value)
    {
        $this->logger->info('[Magewire AuthorizeNet] Checkbox updated to: ' . var_export($value, true));
        
        // Store immediately in session when checkbox changes
        $encryptionKey = $this->_helperData->getEncryptionKey();
        $checkboxValue = $value ? 'true' : 'false';
        $this->checkoutSession->setData('saveCardCheckbox', $this->_encrypt($checkboxValue, $encryptionKey));
        
        $this->logger->info('[Magewire AuthorizeNet] Checkbox immediately stored in session as: ' . $checkboxValue);
    }

    public function createCharge()
    {
        try {
            $encryptionKey = $this->_helperData->getEncryptionKey();
            $cardNumber    = $this->_decryptSession('B4yPd7T5mWuR', $encryptionKey);
            $expMonth      = $this->_decryptSession('V3g6dC4hQ9m8X2L5', $encryptionKey);
            $expYear       = $this->_decryptSession('L8wKSe1cUVm', $encryptionKey);
            $cvv           = $this->_decryptSession('kzgD3B7n', $encryptionKey);
            $grandTotal    = $this->_decryptSession('grandTotal', $encryptionKey);
            $saveCard      = $this->_decryptSession('saveCardCheckbox', $encryptionKey);

            // Step 1: Process payment
            $paymentResponse = $this->_authorizeNetApi->createTransactionRequest(
                $cardNumber,
                $expMonth,
                $expYear,
                $cvv,
                $grandTotal
            );

            if (!$paymentResponse || $paymentResponse->getTransactionResponse() === null) {
                throw new Exception('Transaction failed or returned empty response.');
            }

            $transactionId = $paymentResponse->getTransactionResponse()->getTransId();

            // Step 2: ONLY create Customer Profile if checkbox was checked
            $profileResponse = null;
            if ($saveCard === 'true' || $saveCard === true || $saveCard === '1') {
                try {
                    $profileResponse = $this->_authorizeNetApi->createCustomerProfileFromTransaction($transactionId);
                    $this->logger->info('[Magewire AuthorizeNet] Profile created - checkbox was checked');
                } catch (Exception $e) {
                    $this->logger->error('[Magewire AuthorizeNet] Profile creation failed: ' . $e->getMessage());
                }
            } else {
                $this->logger->info('[Magewire AuthorizeNet] Card saving skipped - checkbox not checked');
            }

            return $profileResponse;

        } catch (Exception $e) {
            $this->logger->error('[Magewire AuthorizeNet] Error in createCharge: ' . $e->getMessage());
            return false;
        }
    }

    private function _decryptSession($key, $encryptionKey)
    {
        $encrypted = $this->checkoutSession->getData($key);
        if (empty($encrypted)) {
            return null;
        }
        
        $method    = 'aes-256-cbc';
        $key       = substr(hash('sha256', $encryptionKey), 0, 32);
        $decoded   = base64_decode($encrypted);
        $iv        = substr($decoded, 0, 16);
        $data      = substr($decoded, 16);
        return openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    private function _storeDataInSessionVariable()
    {
        // DEBUG LOG
        $this->logger->info('[Magewire AuthorizeNet] Storing card data in session', [
            'card_last4' => substr($this->cardNumber ?? 'NULL', -4),
            'checkbox_value_in_property' => $this->checkboxChecked,
        ]);

        // Perform validation
        $encryptionKey      = $this->_helperData->getEncryptionKey();
        $quoteId            = $this->checkoutSession->getQuoteId();
        $quote              = $this->quoteRepository->get($quoteId);
        $this->_grandTotal  = $quote->getGrandTotal();
        
        // Store card data in the session
        $this->checkoutSession->setData('B4yPd7T5mWuR',         $this->_encrypt($this->cardNumber   ?? 0,   $encryptionKey));
        $this->checkoutSession->setData('V3g6dC4hQ9m8X2L5',     $this->_encrypt($this->expireMonth  ?? 0,   $encryptionKey));
        $this->checkoutSession->setData('L8wKSe1cUVm',          $this->_encrypt($this->expireYear   ?? 0,   $encryptionKey));
        $this->checkoutSession->setData('kzgD3B7n',             $this->_encrypt($this->cardCvv      ?? 0,   $encryptionKey));
        $this->checkoutSession->setData('grandTotal',           $this->_encrypt($this->_grandTotal  ?? 0,   $encryptionKey));
        
        // Re-read checkbox from session (it was stored in updatedCheckboxChecked)
        // $storedCheckbox = $this->checkoutSession->getData('saveCardCheckbox');
        // if ($storedCheckbox) {
        //     $this->logger->info('[Magewire AuthorizeNet] Checkbox already in session from updatedCheckboxChecked()');
        // } else {
        //     // Fallback: if not already stored, store it now
        //     $checkboxValue = $this->checkboxChecked ? 'true' : 'false';
        //     $this->checkoutSession->setData('saveCardCheckbox', $this->_encrypt($checkboxValue, $encryptionKey));
        //     $this->logger->info('[Magewire AuthorizeNet] Checkbox stored as fallback: ' . $checkboxValue);
        // }
        $checkboxValue = $this->checkboxChecked ? 'true' : 'false';
        $this->checkoutSession->setData(
            'saveCardCheckbox',
            $this->_encrypt($checkboxValue, $encryptionKey)
        );
    }

    private function _encrypt($dataString, $key)
    {
        $method             = 'aes-256-cbc';
        $key                = substr(hash('sha256', $key), 0, 32);
        $iv                 = random_bytes(16);
        $encryptedData      = openssl_encrypt($dataString, $method, $key, OPENSSL_RAW_DATA, $iv);
        $encryptedString    = base64_encode($iv . $encryptedData);
        return base64_encode($iv . $encryptedData);
    }

    // public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    // {
    //     // DEBUG LOG
    //     $this->logger->info('[Magewire AuthorizeNet] evaluateCompletion called', [
    //         'checkbox_value_in_property' => $this->checkboxChecked,
    //         'card_last4' => substr($this->cardNumber ?? 'NULL', -4),
    //     ]);

    //     // Store card data (checkbox was already stored in updatedCheckboxChecked)
    //     $this->_storeDataInSessionVariable();

    //     return $resultFactory->createSuccess();
    // }
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        $this->logger->info('[Magewire AuthorizeNet] evaluateCompletion called', [
            'checkbox_value_in_property' => $this->checkboxChecked,
            'card_last4' => substr($this->cardNumber ?? 'NULL', -4),
        ]);

        $this->_storeDataInSessionVariable();

        return $resultFactory->createSuccess();
    }
}