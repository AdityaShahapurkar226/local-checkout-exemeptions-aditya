<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Rewrite\Model\Quote\Address;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Customer\Api\AddressRepositoryInterface;

class BillingAddressPersister extends \Magento\Quote\Model\Quote\Address\BillingAddressPersister
{
    /**
     * @var QuoteAddressValidator
     */
    private $addressValidator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param QuoteAddressValidator $addressValidator
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        QuoteAddressValidator $addressValidator,
        AddressRepositoryInterface $addressRepository
    ) {
        parent::__construct(
            $addressValidator,
            $addressRepository
        );
        $this->addressValidator = $addressValidator;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Save address for billing.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param bool $usedForShipping
     * @return void
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function save(CartInterface $quote, AddressInterface $address, $usedForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $customerAddressIdty = $address->getCustomerAddressId();
        $shippingAddres = null;
        $addressData = [];

        if ($usedForShipping) {
            $shippingAddres = $address;
        }
        $saveInAddressBok = $address->getSaveInAddressBook() ? 1 : 0;
        if ($customerAddressIdty) {
            try {
                $addressData = $this->addressRepository->getById($customerAddressIdty);
            } catch (NoSuchEntityException $e) {
                $addressData = [];
                // do nothing if customer is not found by id
            }
            $address = $quote->getBillingAddress()->importCustomerAddressData($addressData);
            if ($usedForShipping) {
                $shippingAddres = $quote->getShippingAddress()->importCustomerAddressData($addressData);
                $shippingAddres->setSaveInAddressBook($saveInAddressBok);
            }
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSaveInAddressBook($saveInAddressBok);
        $quote->setBillingAddress($address);
        if ($usedForShipping) {
            $shippingAddres->setSameAsBilling(1);
            $shippingAddres->setCollectShippingRates(true);
            $quote->setShippingAddress($shippingAddres);
        }
    }
}
