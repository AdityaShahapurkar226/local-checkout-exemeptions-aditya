<?php
namespace Ahy\CharityAndDonation\Model\Total;
use Magento\Quote\Api\CartRepositoryInterface;

class Donation extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
/**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    protected $donationAmount;
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        CartRepositoryInterface $quoteRepository
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->quoteRepository = $quoteRepository;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        // var_dump('something');
        //code to make sure we don't double deduct or add our value
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        // var_dump(count($items));
        if (!count($items)) {
            return $this;
        }

        $exist_amount = 0;
        // Use the donation amount set in the quote
        $donationAmount = $quote->getData('donation_amount') ?? 0;

        $donationAmount = $donationAmount - $exist_amount;

        // var_dump($donationAmount);

        $total->setTotalAmount('donation_amount', $donationAmount);
        $total->setBaseTotalAmount('donation_amount', $donationAmount);

        $total->setDonationAmount($donationAmount);
        $total->setBaseDonationAmount($donationAmount);

        // Add donation to the grand total only for one address
        //Get hasDonationAppliedFlag from quote

        $hasDonationAppliedFlag = $quote->getData('hasDonationAppliedFlag');
        $hasDonationAppliedFlag = boolval($hasDonationAppliedFlag);
        // var_dump($hasDonationAppliedFlag);

        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == 'shipping' && $hasDonationAppliedFlag == false) {

            $total->setGrandTotal($total->getGrandTotal() + $donationAmount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $donationAmount);

            //set has Donation Applied flag in quote to true
            $quote->setData('hasDonationAppliedFlag', 1);
            $quote->save();
            $this->quoteRepository->save($quote);
        }

        return $this;
    }

    public function setDonationAmount($donationAmount)
    {
        $this->donationAmount = $donationAmount;
        return $this->donationAmount;

    }

    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @para Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code'  => 'donation_amount',
            'title' => 'Donation Amount',
            'value' => $quote->getData('donation_amount') ?? 0
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Donation Amount');
    }
}
