<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Model\Shipping;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelGenerator extends \Magento\Shipping\Model\Shipping\LabelGenerator
{

    /**
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param LabelsFactory $labelFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Shipping\LabelsFactory $labelFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Webkul\MarketplaceBaseShipping\Model\Shipping\LabelsFactory $wklabelFactory,
        \Webkul\Marketplace\Model\OrdersFactory $marketplaceOrderFactory,
        \Webkul\MarketplaceBaseShipping\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct(
            $carrierFactory,
            $labelFactory,
            $scopeConfig,
            $trackFactory,
            $filesystem
        );
        $this->wklabelFactory = $wklabelFactory;
        $this->_carrierFactory = $carrierFactory;
        $this->marketplaceOrderFactory = $marketplaceOrderFactory;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(\Magento\Sales\Model\Order\Shipment $shipment, RequestInterface $request)
    {
        $order = $shipment->getOrder();
        $this->helper->getCarrierCode($order);
        $wkcarrier = $this->_carrierFactory->create($this->helper->getCarrierCode($order));
        
        if (!$wkcarrier->isShippingLabelsAvailable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Shipping labels is not available.'));
        }
        $shipment->setPackages($request->getParam('packages'));

        $response = $this->wklabelFactory->create()->requestToShipmentBySeller($shipment);

        if ($response->hasErrors()) {
            throw new \Magento\Framework\Exception\LocalizedException(__($response->getErrors()));
        }
        if (!$response->hasInfo()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response info is not exist.'));
        }
        $labelsContent = [];
        $trackingNumbers = [];
        $info = $response->getInfo();
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }

        $outputPdf = $this->combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render());
        $wkcarrierCode = $wkcarrier->getCarrierCode();

        $sellerOrders = $this->marketplaceOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $this->_customerSession->getCustomerId()])
            ->addFieldToFilter('order_id', ['eq' => $order->getId()]);

        foreach ($sellerOrders as $row) {
            $row->setShipmentLabel($outputPdf->render());
            $row->save();
        }

        $carrierTitle = $this->scopeConfig->getValue(
            'carriers/' . $wkcarrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $shipment->getStoreId()
        );
        if (!empty($trackingNumbers)) {
            $this->addSellerTrackingNumbersToShipment($shipment, $trackingNumbers, $wkcarrierCode, $carrierTitle);
        }
    }

    private function addSellerTrackingNumbersToShipment(
        \Magento\Sales\Model\Order\Shipment $shipment,
        $trackingNumbers,
        $carrierCode,
        $carrierTitle
    ) {
        foreach ($trackingNumbers as $number) {
            if (is_array($number)) {
                $this->addSellerTrackingNumbersToShipment($shipment, $number, $carrierCode, $carrierTitle);
            } else {
                $shipment->addTrack(
                    $this->trackFactory->create()
                        ->setNumber($number)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle)
                );
            }
        }
    }
}
