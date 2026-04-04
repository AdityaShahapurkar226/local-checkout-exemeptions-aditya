<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;

class SuccessMessage extends Template
{
    private CheckoutSession $checkoutSession;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    public function getOrderHoldMessage(): ?string
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        if (!$lastOrderId) {
            return null;
        }

        try {
            $order = $this->orderRepository->get($lastOrderId);
            $status = $order->getStatus(); // e.g., partially_on_hold
            $level = $order->getData('orchid_restriction_level');

            if (in_array($status, ['partially_on_hold', 'compliance_hold', 'orchid_fail_restriction'])) {
                return '<span class="underline underline-weight">Note</span>: Your order has been placed on hold to ensure compliance with applicable regulations and restrictions.<br> Processing will resume once the review is complete. We’ll reach out if additional information is required.';
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}




