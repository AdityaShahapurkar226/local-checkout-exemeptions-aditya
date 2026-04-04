<?php

namespace Ahy\EstateApiIntegration\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Block\Template;
use Psr\Log\LoggerInterface;

class RestrictionInfo extends Template
{
    protected $orderRepository;
    protected $logger;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');

        $this->logger->info("RestrictionInfo Block Loaded. Order ID: " . $orderId);

        try {
            $order = $this->orderRepository->get($orderId);
            $this->logger->info("Order Loaded Successfully. Restriction Level: " . $order->getOrchidRestrictionLevel());
            return $order;
        } catch (\Exception $e) {
            $this->logger->error("Failed to load order in RestrictionInfo block: " . $e->getMessage());
        }

        return null;
    }
}
