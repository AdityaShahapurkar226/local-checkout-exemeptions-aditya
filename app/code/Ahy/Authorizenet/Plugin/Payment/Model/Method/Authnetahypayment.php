<?php
namespace Ahy\Authorizenet\Plugin\Payment\Model\Method;

use Ahy\Authorizenet\Model\ConfigProvider;

class Authnetahypayment
{
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function afterIsActive(\Magento\Payment\Model\Method\AbstractMethod $subject, $result)
    {
        if ($subject->getCode() === 'authnetahypayment') {
            return $this->configProvider->isPaymentMethodEnabled();
        }
        return $result;
    }
}
