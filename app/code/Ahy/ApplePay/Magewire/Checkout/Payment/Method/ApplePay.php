<?php
declare(strict_types=1);

namespace Ahy\ApplePay\Magewire\Checkout\Payment\Method;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;

class ApplePay extends Component
{
    protected $listeners = [
        'billing_address_activated' => 'refresh',
        'shipping_method_selected' => 'refresh',
        'payment_method_selected' => 'refresh',
        'coupon_code_applied' => 'refresh',
        'coupon_code_revoked' => 'refresh'
    ];

    /**
     * @var Session
     */
    private Session $sessionCheckout;

    /**
     * @param Session $sessionCheckout
     */
    public function __construct(
        Session $sessionCheckout
    ) {
        $this->sessionCheckout = $sessionCheckout;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function setToken(string $token): void
    {
        $this->sessionCheckout
            ->getQuote()
            ->getPayment()
            ->setAdditionalInformation(
                'payment_method_nonce',
                $token
            )->save();
    }
}
