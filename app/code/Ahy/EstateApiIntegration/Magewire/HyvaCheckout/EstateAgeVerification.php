<?php

namespace Ahy\EstateApiIntegration\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Hyva\Checkout\ViewModel\Breadcrumbs as CheckoutStepCounter;
use Ahy\EstateApiIntegration\ViewModel\CheckoutRestrictions;
use Ahy\EstateApiIntegration\Logger\Logger;

class EstateAgeVerification extends Component implements EvaluationInterface
{
    public $age;
    public $selectedMonth;
    public $selectedDay;
    public $selectedYear;

    public $asPurchaser          = false;
    public $fireArmsWarranty     = false;
    public $fireArmsReturnPolicy = false;
    public $agreeCompliance      = false;
    
    // Add property for document upload status
    public $isDocumentUploaded   = false;

    protected $dateTime;
    protected $timezone;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $extensionAttributesFactory;
    protected $checkoutStepCounter;
    protected $checkoutRestrictions;
    protected $logger;

    protected $listeners = [
        'updateAgreeCompliance',
        'updateFireArmsWarranty',
        'updateFireArmsReturnPolicy',
        'updateAsPurchaser',
        'documentUploaded', // Add listener for document upload
    ];

    public function __construct(
        DateTime                   $dateTime,
        Session                    $checkoutSession,
        ExtensionAttributesFactory $extensionAttributesFactory,
        CartRepositoryInterface    $quoteRepository,
        TimezoneInterface          $timezone,
        CheckoutStepCounter        $checkoutStepCounter,
        CheckoutRestrictions       $checkoutRestrictions,
        Logger                     $logger
    ) {
        $this->dateTime                   = $dateTime;
        $this->timezone                   = $timezone;
        $this->checkoutSession            = $checkoutSession;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->quoteRepository            = $quoteRepository;
        $this->checkoutStepCounter        = $checkoutStepCounter;
        $this->checkoutRestrictions       = $checkoutRestrictions;
        $this->logger                     = $logger;
    }

    /**
     * Mount method - called when component is initialized
     */
    public function mount()
    {
        // Check if document is already uploaded from session
        $this->isDocumentUploaded = $this->checkoutRestrictions->isDocumentUploaded();
        
        $this->logger->info('[Estate][AgeVerification] Component mounted', [
            'isDocumentUploaded' => $this->isDocumentUploaded,
            'age' => $this->age,
            'agreeCompliance' => $this->agreeCompliance
        ]);
    }

    /* ---------------------------
       Checkbox listeners
    ---------------------------- */

    public function updateAgreeCompliance($value)
    {
        $this->agreeCompliance = (bool) $value;
        $this->logger->info('[Estate][AgeVerification] Compliance updated', ['value' => $this->agreeCompliance]);
    }

    public function updateFireArmsWarranty($value)
    {
        $this->fireArmsWarranty = (bool) $value;
        $this->logger->info('[Estate][AgeVerification] Warranty updated', ['value' => $this->fireArmsWarranty]);
    }

    public function updateFireArmsReturnPolicy($value)
    {
        $this->fireArmsReturnPolicy = (bool) $value;
        $this->logger->info('[Estate][AgeVerification] Return policy updated', ['value' => $this->fireArmsReturnPolicy]);
    }

    public function updateAsPurchaser($value)
    {
        $this->asPurchaser = (bool) $value;
        $this->logger->info('[Estate][AgeVerification] As purchaser updated', ['value' => $this->asPurchaser]);
    }

    /**
     * Listener for document upload event
     */
    public function documentUploaded($status)
    {
        $this->isDocumentUploaded = (bool) $status;
        
        // Also update from session to be sure
        if ($status) {
            $this->checkoutSession->setDocumentUploaded(true);
            $this->checkoutSession->setDocumentUploadedAt(date('Y-m-d H:i:s'));
        }
        
        $this->logger->info('[Estate][AgeVerification] Document uploaded event', [
            'status' => $status,
            'isDocumentUploaded' => $this->isDocumentUploaded
        ]);
    }

    /* ---------------------------
       Checkout steps count
    ---------------------------- */

    public function getCheckoutStepCount(): int
    {
        return count(
            $this->checkoutStepCounter
                ->getCheckoutConfig()
                ->getSteps()
        );
    }

    /* ---------------------------
       Age calculation logic
    ---------------------------- */

    public function calculateAge()
    {
        if (!$this->selectedMonth || !$this->selectedDay || !$this->selectedYear) {
            return;
        }

        $selectedDate = sprintf(
            '%s-%s-%s',
            $this->selectedYear,
            $this->selectedMonth,
            $this->selectedDay
        );

        $birthDate = new \DateTime($selectedDate);
        $today     = new \DateTime('today');

        $this->age = $birthDate->diff($today)->y;

        $quote = $this->checkoutSession->getQuote();

        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory
                ->create(\Magento\Quote\Api\Data\CartExtensionInterface::class);
        }

        if ($this->age >= 18) {
            $extensionAttributes->setAgeVerified(true);
            $extensionAttributes->setAgeOfPurchaser($this->age);
            $quote->setAgeVerified(true);
            $quote->setAgeOfPurchaser($this->age);
        } else {
            $extensionAttributes->setAgeVerified(false);
            $extensionAttributes->setAgeOfPurchaser($this->age);
            $quote->setAgeVerified(false);
            $quote->setAgeOfPurchaser($this->age);
        }

        $quote->setExtensionAttributes($extensionAttributes);
        $this->quoteRepository->save($quote);
        $quote->save();
        
        $this->logger->info('[Estate][AgeVerification] Age calculated', [
            'age' => $this->age,
            'verified' => $this->age >= 18
        ]);
    }

    /* ---------------------------
       Hyvä checkout validation
    ---------------------------- */

    public function evaluateCompletion(
        EvaluationResultFactory $resultFactory
    ): EvaluationResultInterface {

        $checkoutStepCount = $this->getCheckoutStepCount();
        
        $this->logger->info('[Estate][AgeVerification] Evaluate completion START', [
            'checkoutStepCount' => $checkoutStepCount,
            'age' => $this->age,
            'agreeCompliance' => $this->agreeCompliance,
            'asPurchaser' => $this->asPurchaser,
            'fireArmsWarranty' => $this->fireArmsWarranty,
            'fireArmsReturnPolicy' => $this->fireArmsReturnPolicy,
            'isDocumentUploaded' => $this->isDocumentUploaded
        ]);

        // Check if document upload is required (for ADE restriction)
        $isDocumentRequired = false;
        try {
            $isDocumentRequired = $this->checkoutRestrictions->isDocumentUploadRequired();
            $this->logger->info('[Estate][AgeVerification] Document requirement check', [
                'isRequired' => $isDocumentRequired,
                'ageRestriction' => $this->checkoutRestrictions->getAgeRestriction()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[Estate][AgeVerification] Error checking document requirement', [
                'error' => $e->getMessage()
            ]);
        }

        if ($isDocumentRequired) {
            $sessionUploaded = (bool) $this->checkoutSession->getDocumentUploaded();
            $viewModelUploaded = $this->checkoutRestrictions->isDocumentUploaded();
            
            $this->logger->info('[Estate][AgeVerification] Document upload status', [
                'isDocumentUploaded' => $this->isDocumentUploaded,
                'sessionUploaded' => $sessionUploaded,
                'viewModelUploaded' => $viewModelUploaded
            ]);
            
            if (!$this->isDocumentUploaded && !$sessionUploaded && !$viewModelUploaded) {
                $this->logger->warning('[Estate][AgeVerification] Document validation FAILED');
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('document:validation:required')
                    ->withMessage((string) __('Please upload the required document before proceeding.'));
            }
        }

        // 3-step checkout
        if ($checkoutStepCount === 3) {
            
            $this->logger->info('[Estate][AgeVerification] 3-step checkout validation');

            // First check age validation
            if (!$this->age) {
                $this->logger->warning('[Estate][AgeVerification] Age not entered');
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('ENTER YOUR D.O.B.'));
            }

            if ($this->age < 18) {
                $this->logger->warning('[Estate][AgeVerification] Age < 18', ['age' => $this->age]);
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('YOU ARE NOT ELIGIBLE TO BUY RESTRICTED PRODUCT'));
            }

            // Then check compliance agreement
            if (!$this->agreeCompliance) {
                $this->logger->warning('[Estate][AgeVerification] Compliance not agreed');
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('TO PROCEED FURTHER YOU HAVE TO AGREE THE COMPLIANCES'));
            }

            $this->logger->info('[Estate][AgeVerification] 3-step validation PASSED');
            return $resultFactory->createSuccess();
        }

        // 4-step checkout
        if ($checkoutStepCount === 4) {
            
            $this->logger->info('[Estate][AgeVerification] 4-step checkout validation');

            // First check age validation
            if (!$this->age) {
                $this->logger->warning('[Estate][AgeVerification] Age not entered');
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('ENTER YOUR D.O.B.'));
            }

            if ($this->age < 18) {
                $this->logger->warning('[Estate][AgeVerification] Age < 18', ['age' => $this->age]);
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('YOU ARE NOT ELIGIBLE TO BUY RESTRICTED PRODUCT'));
            }

            // Then check all compliance checkboxes
            if (
                !$this->asPurchaser ||
                !$this->fireArmsWarranty ||
                !$this->fireArmsReturnPolicy ||
                !$this->agreeCompliance
            ) {
                $this->logger->warning('[Estate][AgeVerification] Not all compliances agreed', [
                    'asPurchaser' => $this->asPurchaser,
                    'fireArmsWarranty' => $this->fireArmsWarranty,
                    'fireArmsReturnPolicy' => $this->fireArmsReturnPolicy,
                    'agreeCompliance' => $this->agreeCompliance
                ]);
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('TO PROCEED FURTHER YOU HAVE TO AGREE ALL THE COMPLIANCES'));
            }

            $this->logger->info('[Estate][AgeVerification] 4-step validation PASSED');
            return $resultFactory->createSuccess();
        }

        $this->logger->info('[Estate][AgeVerification] Default validation PASSED (no specific step count)');
        return $resultFactory->createSuccess();
    }
}