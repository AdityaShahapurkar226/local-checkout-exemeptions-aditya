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

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Store\Model\ScopeInterface;

/**
 * Shipping labels model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Labels extends \Magento\Shipping\Model\Shipping\Labels
{
        /**
         * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
         * @param \Magento\Shipping\Model\Config $shippingConfig
         * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
         * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
         * @param \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory
         * @param \Magento\Directory\Model\RegionFactory $regionFactory
         * @param \Magento\Framework\Math\Division $mathDivision
         * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
         * @param \Magento\Backend\Model\Auth\Session $authSession
         * @param \Magento\Shipping\Model\Shipment\Request $request
         * @SuppressWarnings(PHPMD.ExcessiveParameterList)
         */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository $dataRepository,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Shipping\Model\Shipping\LabelsFactory $labelFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        Request $request,
        \Webkul\MarketplaceBaseShipping\Helper\Data $helper
    ) {
        parent::__construct(
            $scopeConfig,
            $shippingConfig,
            $storeManager,
            $carrierFactory,
            $rateResultFactory,
            $shipmentRequestFactory,
            $regionFactory,
            $mathDivision,
            $stockRegistry,
            $authSession,
            $request
        );
        $this->dataRepository = $dataRepository;
        $this->_customerSession = $customerSession;
        $this->labelFactory = $labelFactory;
        $this->helper = $helper;
    }

    /**
     * Prepare and do request to shipment
     *
     * @param Shipment $orderShipment
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function requestToShipmentBySeller(Shipment $orderShipment)
    {
        $order = $orderShipment->getOrder();
       
        $wkshippingMethod = $this->helper->getShippingMethod($order);
        $storeId = $orderShipment->getStoreId();
        $shipmentCarrier = $this->_carrierFactory->create($this->helper->getCarrierCode($order));
        $baseCurrencyCode = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            throw new LocalizedException(__('Invalid carrier: %1', $wkshippingMethod->getCarrierCode()));
        }
        $originAddress = $this->dataRepository->getBySellerId(
            $this->_customerSession->getCustomerId(),
            $storeId
        );
        
        $shipperRegionCode = $originAddress->getRegionCode();
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->_regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $originStreet1 = $originAddress->getStreet1();
        if (!$originAddress->getTelephone()
            || !$originStreet1
            || !$originAddress->getCity()
            || !$originAddress->getPostalCode()
            || !$originAddress->getCountryId()
        ) {
            throw new LocalizedException(
                __(
                    'We don\'t have enough information to create shipping labels. Please make 
                    sure your store information and settings are complete.'
                )
            );
        }

        /** @var $request \Magento\Shipping\Model\Shipment\Request */
        $request = $this->_shipmentRequestFactory->create();
        $request->setOrderShipment($orderShipment);
    
        $destaddress = $order->getShippingAddress();
        $this->setShipperSellerDetails($request, $originAddress, $storeId, $shipperRegionCode, $originStreet1);
        $this->setRecipientDetails($request, $destaddress);

        $request->setShippingMethod($wkshippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($storeId);

        return $shipmentCarrier->requestToShipment($request);
    }

    public function setShipperSellerDetails(
        Request $request,
        $originAddress,
        $shipmentStoreId,
        $shipperRegionCode,
        $originStreet
    ) {

        $originStreet2 = $originAddress->getStreet2();
        $seller = $this->_customerSession->getCustomer();
        $request->setShipperContactPersonName($seller->getName());
        $request->setShipperContactPersonFirstName($seller->getFirstname());
        $request->setShipperContactPersonLastName($seller->getLastname());
        $request->setShipperContactCompanyName($originAddress->getCompany());
        $request->setShipperContactPhoneNumber($originAddress->getTelephone());
        $request->setShipperEmail($seller->getEmail());
        $request->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity($originAddress->getCity());
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode($originAddress->getPostalCode());
        $request->setShipperAddressCountryCode($originAddress->getCountryId());
    }
}
