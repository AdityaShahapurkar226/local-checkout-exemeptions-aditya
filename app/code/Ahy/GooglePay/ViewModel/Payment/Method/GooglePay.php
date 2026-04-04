<?php
declare(strict_types=1);

namespace Ahy\GooglePay\ViewModel\Payment\Method;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;
use PayPal\Braintree\Model\Adminhtml\Source\GooglePayBtnColor;
use PayPal\Braintree\Model\GooglePay\Config as GooglePayConfig;
use PayPal\Braintree\Model\GooglePay\Ui\ConfigProvider as GooglePayUiConfig;

class GooglePay implements ArgumentInterface
{
    private LoggerInterface $logger;

    /**
     * @var GooglePayConfig
     */
    private GooglePayConfig $googlePayConfig;

    /**
     * @var GooglePayUiConfig
     */
    private GooglePayUiConfig $googlePayUiConfig;

    /**
     * @param GooglePayConfig $googlePayConfig
     * @param GooglePayUiConfig $googlePayUiConfig
     */
    public function __construct(
        GooglePayConfig $googlePayConfig,
        GooglePayUiConfig $googlePayUiConfig,
        LoggerInterface $logger
    ) {
        $this->googlePayConfig = $googlePayConfig;
        $this->googlePayUiConfig = $googlePayUiConfig;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->googlePayConfig->isActive();
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->googlePayConfig->getMerchantId();
    }

    /**
     * @return string
     */
    public function getClientToken(): string
    {
        return $this->googlePayUiConfig->getClientToken();
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        try {
            return $this->googlePayConfig->getEnvironment();
        } catch (InputException | NoSuchEntityException $e) {
            $this->logger->error('Google Pay environment config is missing: ' . $e->getMessage());
            return 'TEST';
        }
    }

    /**
     * @return int
     */
    public function getButtonColor(): int
    {
        return $this->googlePayConfig->getBtnColor();
    }

    /**
     * @return array
     */
    public function getAvailableCardTypes(): array
    {
        return $this->googlePayConfig->getAvailableCardTypes();
    }

    /**
     * @return string
     */
    public function getPaymentMarkImageSrc(): string
    {
        return $this->googlePayUiConfig->getPaymentMarkSrc();
    }
}
