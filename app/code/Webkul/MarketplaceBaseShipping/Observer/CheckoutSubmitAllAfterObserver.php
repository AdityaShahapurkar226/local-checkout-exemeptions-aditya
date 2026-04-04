<?php
/**
 * Webkul Software Pvt. Ltd.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceBaseShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    private $request;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var OrdersFactory
     */
    protected $mpOrdersFactory;

    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $session;

    /**
     * @var $orderIndex
     */
    private $orderIndex;

    /**
     * @var $orderSequence
     */
    private $orderSequence;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrdersFactory,
        Session $session,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->session = $session;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->logger = $logger;
    }

    /**
     * execute
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->orderIndex = [];
            $this->orderSequence = 0;
            if ($this->session->getShippingInformation() != null) {
                $order = $observer->getEvent()->getOrder();
                if ($order == null) {
                    $orders = $observer->getEvent()->getOrders();
                    foreach ($orders as $ord) {
                        $this->addShippingCharges($ord);
                        $this->orderSequence++;
                    }
                    $this->session->unsShippingInformation();
                } else {
                    $this->addShippingCharges($order);
                    $this->session->unsShippingInformation();
                }
            }
        } catch (\Exception $ex) {
            $this->logger->info($ex->getMessage());
        }
    }

    /**
     * add shipping charges
     * @param object $order
     * @return void
     */
    public function addShippingCharges($order)
    {
        $shippingMethod = $order->getShippingMethod();
        $lastOrderId = $order->getId();
        $shippingInformation = $this->session->getShippingInformation();

        foreach (array_keys($shippingInformation) as $carrierCode) {
            if (strpos($shippingMethod, $carrierCode) !== false) {
                $shipMethod = explode('_', $shippingMethod, 2);

                foreach ((array)$shippingInformation[$carrierCode] as $key => $shipData) {
                    /*** Backward Compatibility Start ***/
                    $flag = false;
                    if (!isset($shipData['sellerCredentials'])) {
                        $flag = true;
                    } elseif ($shipData['sellerCredentials']) {
                        $flag = true;
                    }
                    /*** Backward Compatibility End ***/

                    if ($flag) {
                        $mpOrderCollection = $this->mpOrdersFactory->create()->getCollection()
                            ->addFieldToFilter('order_id', ['eq' => $lastOrderId])
                            ->addFieldToFilter('seller_id', ['eq' => $shipData['seller_id']])
                            ->setPageSize(1);

                        if ($mpOrderCollection->getSize()) {
                            $mpOrder = $mpOrderCollection->getLastItem();
                            $this->setShippingMethod($mpOrder, $shipData, $shipMethod, $key);
                        }
                    }
                }
            }
        }
    }

    /**
     * Save Shipping Carrier and Charges to Marketplace Orders
     * @param Object $mpOrder
     * @param array $shipData
     * @param array $shipMethod
     * @param int $key
     */
    protected function setShippingMethod($mpOrder, $shipData, $shipMethod, $key)
    {
        if (!isset($this->orderIndex[$mpOrder->getId()]) &&
            ($this->orderSequence == 0 || $this->orderSequence == $key) &&
            isset($shipData['submethod'][$shipMethod[1]])
        ) {
            $this->orderIndex[$mpOrder->getId()] = 1;
            $mpOrder->setCarrierName($shipData['submethod'][$shipMethod[1]]['method']);
            $mpOrder->setShippingCharges($shipData['submethod'][$shipMethod[1]]['cost']);
            $mpOrder->save();
        }
    }
}
