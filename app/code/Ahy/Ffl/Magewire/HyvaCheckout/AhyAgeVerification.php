<?php

namespace Ahy\Ffl\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Ahy\Ffl\Model\HyvaCheckout\CustomCondition\IsFflRequired;
use Hyva\Checkout\ViewModel\Breadcrumbs as CheckoutStepCounter;

class AhyAgeVerification extends Component implements EvaluationInterface 
{
    public $age;
    public $selectedMonth;
    public $selectedDay;
    public $selectedYear;
    public $asPurchaser             = false;
    public $fireArmsWarranty        = false;
    public $fireArmsReturnPolicy    = false;
    public $agreeCompliance         = false;

    protected $dateTime;
    protected $timezone;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $extensionAttributesFactory;
    private   $_isFflRequired;
    protected $_checkoutStepCounter;

    protected $listeners = [
        'updateAgreeCompliance',
        'updateFireArmsWarranty',
        'updateFireArmsReturnPolicy',
        'updateAsPurchaser',
    ];

    public function __construct(
        DateTime                    $dateTime,
        Session                     $checkoutSession,
        ExtensionAttributesFactory  $extensionAttributesFactory,
        CartRepositoryInterface     $quoteRepository,
        TimezoneInterface           $timezone,
        IsFflRequired               $isFflRequired,
        CheckoutStepCounter         $checkoutStepCounter
    ) {
        $this->dateTime                     = $dateTime;
        $this->timezone                     = $timezone;
        $this->checkoutSession              = $checkoutSession;
        $this->extensionAttributesFactory   = $extensionAttributesFactory;
        $this->quoteRepository              = $quoteRepository;
        $this->_isFflRequired               = $isFflRequired;
        $this->_checkoutStepCounter         = $checkoutStepCounter;
    }

    public function updateAgreeCompliance($value)
    {
        $this->agreeCompliance = $value;
    }

    public function updateFireArmsWarranty($value)
    {
        $this->fireArmsWarranty = $value;
    }

    public function updateFireArmsReturnPolicy($value)
    {
        $this->fireArmsReturnPolicy = $value;
    }

    public function updateAsPurchaser($value)
    {
        $this->asPurchaser = $value;
    }

    public function getCheckoutStepCount(){
        return count($this->_checkoutStepCounter->getCheckoutConfig()->getSteps());
    }

    public function calculateAge()
    {
        if ($this->selectedMonth && $this->selectedDay && $this->selectedYear) {
            $selectedDate       = "{$this->selectedYear}-{$this->selectedMonth}-{$this->selectedDay}";
            $selectedDateTime   = new \DateTime($selectedDate);
            $today              = new \DateTime('today');
            $birthDate          = $selectedDateTime->diff($today)->y;
            $this->age          = $birthDate;
            // Set the selectedFflCentreId and selectedFflCentreAddress attributes in the quote
            $quote              = $this->checkoutSession->getQuote();
            $extensionAttributes = $quote->getExtensionAttributes();

            // If the extension attributes are null, create a new instance
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->extensionAttributesFactory->create('Magento\Quote\Api\Data\CartExtensionInterface');
            }

            // Update the extension attributes with the new values
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

            // Set the updated extension attributes back to the quote
            $quote->setExtensionAttributes($extensionAttributes);
            $this->quoteRepository->save($quote);
            $quote->save();
            
        }
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        $checkoutStepCount = $this->getCheckoutStepCount();
        if($checkoutStepCount == 3){
            if(!$this->agreeCompliance){
                if ($this->age == 0 || $this->age == null){
                    return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('ENTER YOUR D.O.B.'));
                }else if($this->age < 18){
                    return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('YOU ARE NOT ELIGIBLE TO BUY RESTRICTED PRODUCT'));
                } else{
                    return $resultFactory->createErrorMessageEvent()
                        ->withCustomEvent('age:validation:error')
                        ->withMessage((string) __('TO PROCEED FURTHER YOU HAVE TO AGREE THE COMPLIANCES'));
                }
            }
            return $resultFactory->createSuccess();
        }else if( $checkoutStepCount == 4){
            if(!$this->asPurchaser || !$this->fireArmsWarranty || !$this->fireArmsReturnPolicy || !$this->agreeCompliance){
                if ($this->age == 0 || $this->age == null){
                    return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('ENTER YOUR D.O.B.'));
                }else if($this->age < 18){
                    return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('YOU ARE NOT ELIGIBLE TO BUY RESTRICTED PRODUCT'));
                }
                return $resultFactory->createErrorMessageEvent()
                    ->withCustomEvent('age:validation:error')
                    ->withMessage((string) __('TO PROCEED FURTHER YOU HAVE TO AGREE ALL THE COMPLIANCES'));
            }
            return $resultFactory->createSuccess();
        }
        
    }
}
