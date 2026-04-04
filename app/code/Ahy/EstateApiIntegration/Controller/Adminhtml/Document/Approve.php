<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Controller\Adminhtml\Document;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Approve extends Action
{
    const ADMIN_RESOURCE = 'Ahy_EstateApiIntegration::approve_document';

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Invalid order.'));
            return $this->_redirect('sales/order/index');
        }

        try {
            $order = $this->orderRepository->get($orderId);

            if (!$order->getEstateDocument()) {
                $this->messageManager->addErrorMessage(__('No document found.'));
                return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }

            if ((int) $order->getEstateDocumentApproved() === 1) {
                $this->messageManager->addErrorMessage(__('Already approved.'));
                return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }

            $order->setEstateDocumentApproved(1);
            $order->addCommentToStatusHistory(
                __('Compliance document approved by admin.')
            );

            $this->orderRepository->save($order);

            $this->messageManager->addSuccessMessage(__('Document approved.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Approval failed.'));
        }

        return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
