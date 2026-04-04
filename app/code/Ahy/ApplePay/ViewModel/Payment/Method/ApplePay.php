<?php
declare(strict_types=1);

namespace Ahy\ApplePay\ViewModel\Payment\Method;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PayPal\Braintree\Model\ApplePay\Config as ApplePayConfig;
use PayPal\Braintree\Model\ApplePay\Ui\ConfigProvider as ApplePayUiConfig;

class ApplePay implements ArgumentInterface
{
    /**
     * @var ApplePayConfig
     */
    private ApplePayConfig $applePayConfig;

    /**
     * @var ApplePayUiConfig
     */
    private ApplePayUiConfig $applePayUiConfig;

    /**
     * @param ApplePayConfig $applePayConfig
     * @param ApplePayUiConfig $applePayUiConfig
     */
    public function __construct(
        ApplePayConfig $applePayConfig,
        ApplePayUiConfig $applePayUiConfig
    ) {
        $this->applePayConfig = $applePayConfig;
        $this->applePayUiConfig = $applePayUiConfig;
    }

    /**
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->applePayConfig->getMerchantName();
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getClientToken(): ?string
    {
        try {
            return $this->applePayUiConfig->getClientToken();
        } catch (InputException | NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getPaymentMarkImageSrc(): string
    {
        return $this->applePayUiConfig->getPaymentMarkSrc();
    }
}