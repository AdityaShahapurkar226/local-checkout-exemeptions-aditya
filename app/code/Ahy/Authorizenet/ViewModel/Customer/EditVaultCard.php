<?php
// File: app/code/Ahy/Authorizenet/ViewModel/Customer/EditVaultCard.php

namespace Ahy\Authorizenet\ViewModel\Customer;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

class EditVaultCard implements ArgumentInterface
{
    protected $registry;
    protected $paymentTokenRepository;

    public function __construct(
        Registry $registry,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->registry = $registry;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    public function getTokenId(): ?int
    {
        return $this->registry->registry('ahy_edit_token_id');
    }

    public function getVaultToken(): ?\Magento\Vault\Api\Data\PaymentTokenInterface
    {
        try {
            $id = $this->getTokenId();
            return $id ? $this->paymentTokenRepository->getById($id) : null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function getCardDetails(): array
    {
        $token = $this->getVaultToken();
        if (!$token) {
            return [];
        }

        $details = json_decode($token->getTokenDetails() ?? '{}', true);

        $expirationDate = $details['expirationDate'] ?? '';
        [$month, $year] = explode('/', $expirationDate . '/');

        return [
            'type'            => $details['type'] ?? '',
            'maskedCC'        => $details['maskedCC'] ?? '',
            'expirationDate'  => $expirationDate,
            'expirationMonth' => $month,
            'expirationYear'  => $year,
        ];
    }
}
