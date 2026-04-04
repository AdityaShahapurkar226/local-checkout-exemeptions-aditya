<?php

namespace Ahy\CharityAndDonation\Magewire\HyvaCheckout;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;

class CharityAndDonation extends Component{

    const DONATION_AMOUNT = 1;
    public $grandTotal;
    public $roundUp;
    public $checkboxChecked = false;
    public $isChecked = 'not checked';
    public $refreshFlag = false;

    public ?bool $triggerLoader = null;
    public ?bool $applyDonation = null;
    public ?bool $removeDonation = null;

    protected $checkoutSession;
    protected $quoteRepository;
    protected $listeners = [
        'updateCheckboxChecked'
    ];
    protected $loader = true;
    // protected $loader = [
    //     'checkboxChecked:true' => 'Adding Donation',
    //     'checkboxChecked:false' => 'Removing Donation',
    //    // 'applyDonation' => 'Adding Donation',
    //    // 'removeDonation' => 'Removing Donation'
    // ];

    public function __construct(
        Session                 $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }
    public function updateCheckboxChecked(bool $value)
    {
        $this->checkboxChecked = $value;
        return $this->donationConservation($value);
    }
    
    public function getDonationAmountFromQuote(){
        $quote = $this->checkoutSession->getQuote();
        $donationAmount = (int) $quote->getData('donation_amount') ?? 0;
        return $donationAmount;
    }

    public function donationConservation($value) {
        // dd($value, $this->checkboxChecked);
        if($value){
            $donationAmount = self::DONATION_AMOUNT;
        }else{
            $donationAmount = 0;
        }

        $quote = $this->checkoutSession->getQuote(); // Get the quote
        // Update the donation amount
        $quote->setData('donation_amount', (float) $donationAmount);
        $quote->setDataUsingMethod('hasDonationAppliedFlag', 0);

        // Set flag to recalculate totals and recollect
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        // Save the quote again to persist the changes
        $quote->save();
        $this->quoteRepository->save($quote);

        // $reloadedQuote = $this->quoteRepository->get($quote->getId());
        // dd($reloadedQuote->getData());  // This should output the donation amount you've set

        // Emit event to refresh price summary
        // $this->emit('price-summary.total-segments');
        $this->emit('donation_calculated');
        return $value;  
    }

}
