<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerBuyerCommunication
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;
use Magento\Framework\Message\ManagerInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var SellerBuyerCommunication
     */
    protected $sellerBuyerCommunication;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param SellerBuyerCommunication $sellerBuyerCommunication
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        SellerBuyerCommunication $sellerBuyerCommunication,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->sellerBuyerCommunication = $sellerBuyerCommunication;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $request_data = $this->request->getPost();
            $customer_id = $observer->getCustomer()->getId();
            $sellerData = $this->sellerBuyerCommunication->getCollection();
            $sellerData->addFieldToFilter('email_id', ['eq'=>$request_data['email']]);
            if ($sellerData->getSize()) {
                foreach ($sellerData as $seller) {
                    $seller->setCustomerId($customer_id);
                    $seller->setCustomerName($observer->getCustomer()->getFirstName()
                    ." ".$observer->getCustomer()->getLastName());
                    $seller->save();
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }
}
