<?php
declare(strict_types=1);

namespace Ahy\PaymentIntegrations\ViewModel;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Model\Ui\ConfigProvider;

class Braintree implements ArgumentInterface
{
    private const PATH_SEND_LINE_ITEMS = 'payment/braintree/send_line_items';
    private const PATH_ALWAYS_REQUEST_3DS = 'payment/braintree/always_request_3ds';
    private const XML_CONFIG_PATH_RECAPTCHA = 'recaptcha_frontend/type_for/';

    /**
     * @var BraintreeConfig
     */
    private BraintreeConfig $braintreeConfig;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var Session
     */
    private Session $sessionCheckout;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param Session $sessionCheckout
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BraintreeConfig $braintreeConfig,
        ConfigProvider $configProvider,
        Session $sessionCheckout,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->braintreeConfig = $braintreeConfig;
        $this->configProvider = $configProvider;
        $this->sessionCheckout = $sessionCheckout;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get environment
     *
     * @return string|null
     */
    public function getEnvironment(): ?string
    {
        try {
            return $this->braintreeConfig->getEnvironment();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get merchant id
     *
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        try {
            return $this->braintreeConfig->getMerchantId();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get merchant account id
     *
     * @return string|null
     */
    public function getMerchantAccountId(): ?string
    {
        try {
            return $this->braintreeConfig->getMerchantAccountId();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get client token
     *
     * @return Error|Successful|string|null
     */
    public function getClientToken()
    {
        try {
            return $this->configProvider->getClientToken();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        try {
            return $this->braintreeConfig->isActive();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal(): float
    {
        try {
            return (float) $this->sessionCheckout
                ->getQuote()
                ->getGrandTotal();
        } catch (Exception $e) {
            return 0.00;
        }
    }

    /**
     * Get Currency
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->getQuoteCurrencyCode();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get billing address
     *
     * @return Address|null
     */
    public function getBillingAddress(): ?Address
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->getBillingAddress();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get shipping address
     *
     * @return Address|null
     */
    public function getShippingAddress(): ?Address
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->getShippingAddress();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Check if quote is virtual
     *
     * @return bool
     */
    public function getIsVirtual(): bool
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->isVirtual();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get quote items
     *
     * @return Item[]|false|null
     */
    public function getQuoteItems()
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->getItems();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        try {
            return $this->sessionCheckout
                ->getQuote()
                ->getCustomerEmail();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Is 3DS enabled
     *
     * @return bool
     */
    public function is3dsEnabled(): bool
    {
        try {
            return $this->braintreeConfig->isVerify3DSecure();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Is always request 3DS enabled
     *
     * @return bool
     */
    public function alwaysRequest3ds(): bool
    {
        try {
            return (bool) $this->scopeConfig->getValue(
                self::PATH_ALWAYS_REQUEST_3DS,
                ScopeInterface::SCOPE_STORE
            );
        } catch (LocalizedException | NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Check if customer is guest
     *
     * @return bool
     */
    public function isGuestQuote(): bool
    {
        try {
            return (bool) $this->sessionCheckout
                ->getQuote()
                ->getCustomerIsGuest();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Can send line items
     *
     * @return bool
     */
    public function canSendLineItems(): bool
    {
        try {
            return (bool) $this->scopeConfig->getValue(
                self::PATH_SEND_LINE_ITEMS,
                ScopeInterface::SCOPE_STORE
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get selected type for form
     *
     * @param string $formId
     * @return string|null
     */
    public function getSelectedTypeForForm(string $formId): ?string
    {
        try {
            return $this->scopeConfig->getValue(
                self::XML_CONFIG_PATH_RECAPTCHA . $formId,
                ScopeInterface::SCOPE_STORE
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get threshold amount
     *
     * @return float|null
     */
    public function getThresholdAmount(): ?float
    {
        try {
            return $this->braintreeConfig->getThresholdAmount();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get ReCaptcha site key
     *
     * @param string|null $recaptchaType
     * @return string|null
     */
    public function getReCaptchaSiteKey(string $recaptchaType = null): ?string
    {
        try {
            return $this->scopeConfig->getValue(
                'recaptcha_frontend/type_' . $recaptchaType . '/public_key',
                ScopeInterface::SCOPE_STORE
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get quote applied gift cards
     *
     * @return array
     */
    public function getQuoteGiftCards(): array
    {
        try {
            return array_map(function (array $card): array {
                return [
                    'code'   => $card['c'],
                    'amount' => $card['a'],
                ];
            }, json_decode($this->sessionCheckout->getQuote()->getGiftCards() ?? '', true) ?? []);
        } catch (LocalizedException | NoSuchEntityException $e) {
            return [];
        }
    }
}
