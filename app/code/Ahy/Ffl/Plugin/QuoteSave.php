<?php 

namespace Ahy\Ffl\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartExtensionInterfaceFactory;

class QuoteSave
{
    /**
     * @var CartExtensionInterfaceFactory
     */
    private $cartExtensionFactory;

    /**
     * @param CartExtensionInterfaceFactory $cartExtensionFactory
     */
    public function __construct(
        CartExtensionInterfaceFactory $cartExtensionFactory
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return array
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();

        if ($extensionAttributes !== null) {
            // Here, handle the saving of your custom attributes.
            // For example, if you have a custom attribute called "ageVerified",
            // you can set it as follows:
            $extensionAttributes = $this->cartExtensionFactory->create();
            $extensionAttributes->setAgeVerified(true); // Set your custom attribute value here
            $extensionAttributes->setAgeOfPurchaser(1234); // Set your custom attribute value here
            $extensionAttributes->setFflCentre('ffl center address here'); // Set your custom attribute value here
            $quote->setExtensionAttributes($extensionAttributes);
            $quote->save();
        }

        return [$quote];
    }
}
