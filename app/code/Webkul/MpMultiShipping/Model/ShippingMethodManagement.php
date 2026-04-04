<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;

/**
 * Shipping method read service.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected $_isCart = false;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Webkul\MpMultiShipping\Logger\Logger $logger
     * @param \Magento\Framework\Session\SessionManager $coreSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpMultiShipping\Logger\Logger $logger,
        \Magento\Framework\Session\SessionManager $coreSession
    ) {
        parent::__construct(
            $quoteRepository,
            $converter,
            $addressRepository,
            $totalsCollector
        );
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->mpMultiShipLog = $logger;
        $this->addressRepository = $addressRepository;
        $this->totalsCollector = $totalsCollector;
        $this->_checkoutSession = $checkoutSession;
        $this->coreSession = $coreSession;
    }

    /**
     * estinmate rates by address
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        $this->_isCart = true;
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /**
         *no methods applicable for empty carts or carts with virtual products
         */
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * estimate rates by address id
     *
     * @param int $cartId
     * @param int $addressId
     * @return array
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);
        return $this->getShippingMethods($quote, $address);
    }

   /**
    * Get estimated rates
    *
    * @param Quote $quote
    * @param int $country
    * @param string $postcode
    * @param int $regionId
    * @param string $region
    * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
    */
    protected function getEstimatedRates(
        \Magento\Quote\Model\Quote $quote,
        $country,
        $postcode,
        $regionId,
        $region,
        $address = null
    ) {
        $data = [
            EstimateAddressInterface::KEY_COUNTRY_ID => $country,
            EstimateAddressInterface::KEY_POSTCODE => $postcode,
            EstimateAddressInterface::KEY_REGION_ID => $regionId,
            EstimateAddressInterface::KEY_REGION => $region
        ];
        return $this->getShippingMethods($quote, $data);
    }

    /**
     * Get list of available shipping methods
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $addressData
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, $addressData)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($this->extractAddressData($addressData));
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        $sellerShipping = '';
        $carriertitle = '';
        $methodtitle = '';
        $flag = false;
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                if ($rate->getCarrier() == 'mpmultishipping') {
                    $sellerShipping = $this->_checkoutSession->getSellerMethod();
                    if (count($sellerShipping) > 0) {
                        $flag = false;
                    }
                    $carriertitle = $rate->getCarrierTitle();
                    $methodtitle = $rate->getCarrierTitle();
                    $rate->setMethodTitle($this->getSubMethodsTitle($this->coreSession->getSelectedMethods()));
                }
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        if ($flag) {
            return;
        }
        $output[] = [
            'available' => true,
            'amount' => 0,
            'carrier_code'=>'mpmultishipping',
            'carrier_title' => $carriertitle,
            'method_code' => 'mpmultishipping',
            'method_title' =>  $this->getSubMethodsTitle($this->coreSession->getSelectedMethods()),
            'sellerShipping' => $sellerShipping,
        ];
        return $output;
    }

    /**
     * getSubMethodsTitle
     * @param array $selected
     * @return string $subtitle
     */
    private function getSubMethodsTitle($selected)
    {
        return __("MultiShipping");
    }

    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface  $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 100.2.0
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }
}
