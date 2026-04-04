<?php

namespace Ahy\Ffl\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;
use Ahy\Ffl\Model\ResourceModel\FflCentres\CollectionFactory as FflCentresCollectionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Ahy\ffl\Service\BingMapsApi;
use Magento\Directory\Model\RegionFactory;
use Ahy\Ffl\Block\Frontend\FflSelectionDetails;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class AhyFflSelection extends Component implements EvaluationInterface 
{
    /**
     * @var FflCentresCollectionFactory
     */
    protected $_fflCentresCollectionFactory;
    
    /**
     * @var AddressList
     */
    protected $_addressList;
    protected $cart;
    protected $regionFactory;
    protected $_bingMap;
    protected $eventManager;
    protected $checkoutSession;
    protected $extensionAttributesFactory;
    protected $quoteRepository;
    protected $noFflCentreAvailable = false;
    // protected $loader = [
    //     'updateSelection' => 'Saving FFL Centre Selection'
    // ];
    protected $listeners = ['updateAgreeOnTermAndCondition'];

    public $selectedFflCentreId = 0;
    public $updateSelection;
    public $selectedOption = false;
    public $agreeOnTermAndCondition = false;
    public $selectedFflCentreName;
    public $selectedFflCentreAddressHtml;
    public $addressFfl;
    public $apiKey = 'ApGTO-V0eElZiHvosHcamRrL_NRFo0BRhCLSHnhmLxcT7abLf1L0rR2_-HFzWh_b';
    public $imageUrl = 'https://dev.virtualearth.net/REST/v1/Imagery/Map/Road?mapSize=800,300&pp=';

    /**
     * @param FflCentresInterfaceFactory $fflCentresFactory
     */
    public function __construct(
        FflCentresCollectionFactory $fflCentresCollectionFactory,
        Cart $cart,
        BingMapsApi $bingMap,
        RegionFactory $regionFactory,
        Session $checkoutSession,
        ExtensionAttributesFactory $extensionAttributesFactory,
        CartRepositoryInterface $quoteRepository,
        EventManager $eventManager
    ) {
        $this->_fflCentresCollectionFactory = $fflCentresCollectionFactory;
        $this->cart = $cart;
        $this->_bingMap = $bingMap;
        $this->regionFactory = $regionFactory;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->quoteRepository = $quoteRepository;
    }
    
    public function updateAgreeOnTermAndCondition($value)
    {
        $this->agreeOnTermAndCondition = $value;
    }

    public function getFflCentres($stateName)
    {
        $region = $this->regionFactory->create();
        $region->loadByName($stateName, 'US'); // Assuming the country is the United States (US)

        $regionCode =  $region->getCode();
        $collection = $this->_fflCentresCollectionFactory->create();
        $collection->addFieldToSelect('*'); // Retrieve all fields
        $collection->addFieldToFilter('region_id', $regionCode); // Add filter based on state code
        // $collection->getSelect()->limit(25); // Limit the results to 25 entries
        return $collection;
    }

    public function getFflCentresArr($stateName): array
    {
        $collection = $this->getFflCentres($stateName);
        $fflCentresArr = [];
        
        foreach ($collection as $fflCentre) {
            $fflCentresArr[] = $fflCentre->getData();
        }
        return $fflCentresArr;
    }

    public function getAddressList()
    {
        $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
        return $shippingAddress;
    }

    public function convertAddressToLatLong($userAddressDetailsForFfl): ?array
    {
        return $this->_bingMap->getAddressCoordinates($userAddressDetailsForFfl);
    }

    public function getFflStoreDistanceInMiles($userAddressDetailsForFfl)
    {
        $userLatLong                        = $this->convertAddressToLatLong($userAddressDetailsForFfl);
        $errorMsg = 'We did not find any FFL centre for the provided address. Please recheck and try again.';
        if($userLatLong == null){
            $this->noFflCentreAvailable = true;
            return $errorMsg;
        }
        $fflCentreArrCollection             = $this->getFflCentresArr($userAddressDetailsForFfl['state']);
        $fflStoreDistanceFromUserInMiles    = $this->_bingMap->calculateFflDistanceFromUserInMiles($userLatLong, $fflCentreArrCollection);
        // var_dump($fflStoreDistanceFromUserInMiles); return 'true';
        if($fflStoreDistanceFromUserInMiles == null){
            $this->noFflCentreAvailable = true;
            return $errorMsg;
        }
        $fflStoreDetails                    = [];
        $iterate                            = 0;
        $fflStoreCount                      = 0;

        foreach ($fflCentreArrCollection as $fflCentre) {
            if ($fflStoreDistanceFromUserInMiles[$iterate] > 0 && $fflStoreDistanceFromUserInMiles[$iterate] !== -1) {
                $fflCentre['miles'] = $fflStoreDistanceFromUserInMiles[$iterate];
                $fflStoreDetails[] = $fflCentre;
                $fflStoreCount++;
                if ($fflStoreCount === 25) {
                    break;
                }
            }
            $iterate++;
        }

        return $fflStoreDetails;
    }

    public function selectedFflCentre($selectedFflCentreId, $fflCentreJson)
    {
        $fflCentre = $fflCentreJson;
        $this->addressFfl = $fflCentre;
        $this->selectedFflCentreId = $selectedFflCentreId;
        $this->selectedOption = true;

        // Set the selectedFflCentreId and selectedFflCentreAddress attributes in the quote
        $quote = $this->checkoutSession->getQuote();
        // $extensionAttributes = $quote->getExtensionAttributes();
        // If the extension attributes are null, create a new instance
        // if ($extensionAttributes === null) {
        //     $extensionAttributes = $this->extensionAttributesFactory->create('Magento\Quote\Api\Data\CartInterface');
        // }
        // $extensionAttributes->setFflCentre($fflCentre['CentreName']);
        // $quote->setExtensionAttributes($extensionAttributes);
        // Set FFL Centre values in the quote
        $fflCentreNameAndAddress = $fflCentre['CentreName'] . ': ' . $fflCentre['AddressLine1'] . ', ' . $fflCentre['City'] . ', ' . $fflCentre['region_id'] . ', ' . $fflCentre['zipcode'] . ', US. Phone Number: ' . $fflCentre['phone_no']; 
        $quote->setFflCentre($fflCentreNameAndAddress);
        $this->quoteRepository->save($quote);
        $quote->save();
        // Dispatch the custom event with the updated values
        $this->eventManager->dispatch('ahy_fffl_selection_update', ['ahy_ffl_selection' => $this]);
    }

    public function getSelectedFflCenterFromQuote(){
        $quote = $this->checkoutSession->getQuote();
        $selectedFflCenter = $quote->getFflCentre() ?? 'not selected';
        return $selectedFflCenter;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        if($this->noFflCentreAvailable){
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage((string) __('We did not find any FFL centre for the provided address. Please recheck and try again.'));
        } else if(!$this->selectedOption){
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage((string) __('TO PROCEED FURTHER SELECT THE FFL CENTRE '));
        } else if(!$this->agreeOnTermAndCondition){
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage((string) __('PLEASE ACKNOWLEDGE TO PROCEED FURTHER.'));
        }
        return $resultFactory->createSuccess();
    }

}
