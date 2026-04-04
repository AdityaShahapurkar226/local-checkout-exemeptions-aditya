<?php

namespace Ahy\Authorizenet\Controller\Vault;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class StoreSelected extends Action
{
    protected JsonFactory $resultJsonFactory;
    protected SessionManagerInterface $session;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        // Read POST params (form data)
        $data = $this->getRequest()->getParams();

        if (!isset($data['public_hash'], $data['customer_profile_id'], $data['customer_payment_profile_id'])) {
            return $result->setData(['success' => false, 'message' => 'Missing required fields']);
        }

        // Form key validation is automatically done by Magento on POST request if enabled

        $this->session->setData('authnet_selected_card', [
            'public_hash' => $data['public_hash'],
            'customer_profile_id' => $data['customer_profile_id'],
            'customer_payment_profile_id' => $data['customer_payment_profile_id']
        ]);

        return $result->setData(['success' => true]);
    }
}