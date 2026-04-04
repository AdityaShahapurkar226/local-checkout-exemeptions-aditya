<?php

declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ahy\EfflApiIntegration\Service\CensusGeocodingService;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer\CollectionFactory as DealerCollectionFactory;
use Ahy\EfflApiIntegration\Logger\Logger as EfflLogger;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Ahy\EfflApiIntegration\Service\AzureMapsRouteService;


/**
 * Magewire component that:
 *  - geocodes shipping address (Census API)
 *  - finds nearby dealers (Haversine)
 *  - exposes dealers to template
 *  - handles dealer selection and persists to quote
 *  - validates the checkout step (EvaluationInterface)
 */
class AhyFflSelection extends Component implements EvaluationInterface
{
    /** @var array Nearby dealers (each item contains dealer columns + distance) */
    public $dealers = [];

    /* ----- Magewire state properties exposed to template ----- */
    /** @var int|null Selected dealer entity_id */
    public $selectedFflDealerId = 0;
    /** @var bool Has user clicked/select option */
    public $selectedOption = false;
    /** @var bool Whether user checked the acknowledgement */
    public $agreeOnTermAndCondition = false;
    /** @var bool True when no dealer found for address */
    protected $noFflDealerAvailable = false;
    /** @var mixed Storage for selected dealer full data (array/object) */
    protected $addressFfl = null;
    protected $persistedSelectionMatchesAddress = false;

    /** Dependencies */
    protected $checkoutSession;
    protected $geocodingService;
    protected $dealerCollectionFactory;
    protected $Efflintegrationlogger;
    protected $regionFactory;
    protected $extensionAttributesFactory;
    protected $quoteRepository;
    protected $eventManager;
    protected AzureMapsRouteService $azureMapsService;


    /** Magewire listeners for browser events (if using emit) */
    protected $listeners = ['updateAgreeOnTermAndCondition'];

    /**
     * Constructor
     *
     * Note: adding CartRepositoryInterface + EventManager + ExtensionAttributesFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CensusGeocodingService $geocodingService,
        DealerCollectionFactory $dealerCollectionFactory,
        EfflLogger $Efflintegrationlogger,
        RegionFactory $regionFactory,
        ExtensionAttributesFactory $extensionAttributesFactory,
        CartRepositoryInterface $quoteRepository,
        AzureMapsRouteService $azureMapsService,
        EventManager $eventManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->geocodingService = $geocodingService;
        $this->dealerCollectionFactory = $dealerCollectionFactory;
        $this->Efflintegrationlogger = $Efflintegrationlogger;
        $this->regionFactory = $regionFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->azureMapsService = $azureMapsService;
    }

    /**
     * Mount lifecycle: called when component initializes in browser.
     */
    public function mount()
    {
        $this->Efflintegrationlogger->info('[EfflApiIntegration] Mounting FFL Selection component.');

        // Populate nearby dealers
        $this->fetchNearbyDealers();

        // Hydrate any persisted selection and check if it still matches current shipping address
        $this->hydrateSelectionFromQuote();

        // Add this: If we have a valid selection that matches the address, dispatch the event

        if ($this->persistedSelectionMatchesAddress && $this->addressFfl) {
            $this->dispatchBrowserEvent('ffl:dealer:selected', [
                'dealer' => [
                    'name' => $this->addressFfl['dealer_name'] ?? '',
                    'street' => $this->addressFfl['street'] ?? '',
                    'city' => $this->addressFfl['city'] ?? '',
                    'state' => $this->addressFfl['state'] ?? '',
                    'zip_code' => $this->addressFfl['zip_code'] ?? ''
                ]

            ]);
        } else {
            // Add this: Clear the selection if no valid persisted selection
            $this->dispatchBrowserEvent('ffl:dealer:clear');
        }
    }
    
    /**
     * Fetch shipping address, geocode using Census API, run Haversine SQL and populate $this->dealers
     *
     * Full logging at each step for debugging.
     */
    protected function fetchNearbyDealers()
    {
        $quote = $this->checkoutSession->getQuote();
        $address = $quote->getShippingAddress();

        if (!$address || !$address->getPostcode()) {
            $this->Efflintegrationlogger->info('[EfflApiIntegration] No shipping address or postcode found for quote id: ' . $quote->getId());
            $this->dealers = [];
            return;
        }

        // Convert state name to 2-letter code where possible (Census expects e.g. "TX")
        $region = $this->regionFactory->create()->loadByName($address->getRegion(), 'US');
        $stateCode = $region->getCode() ?: $address->getRegion();

        // Build payload for Census API (structured)
        $addressPayload = [
            'street' => trim($address->getStreetLine(1) ?? ''),
            'city'   => trim($address->getCity() ?? ''),
            'state'  => trim($stateCode ?? ''),
            'zip'    => trim($address->getPostcode() ?? '')
        ];

        $this->Efflintegrationlogger->info('[EfflApiIntegration] Shipping address payload: ' . json_encode($addressPayload));

        // 1) Call Census Geocoding API
        try {
            $response = $this->geocodingService->geocodeAddress($addressPayload);
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Census API response: ' . json_encode($this->truncateForLog($response)));
        } catch (\Throwable $e) {
            $this->Efflintegrationlogger->error('[EfflApiIntegration] Census API call failed: ' . $e->getMessage());
            $this->dealers = [];
            return;
        }

        // Validate response and extract coords
        if (empty($response['result']['addressMatches'][0]['coordinates'])) {
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Geocoding failed or returned empty coordinates.');
            $this->dealers = [];
            $this->noFflDealerAvailable = true;
            return;
        }

        $coords = $response['result']['addressMatches'][0]['coordinates'];
        if (!isset($coords['x'], $coords['y'])) {
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Geocoding returned invalid coordinates structure.', $coords);
            $this->dealers = [];
            $this->noFflDealerAvailable = true;
            return;
        }

        // Census returns X (longitude) and Y (latitude)
        $lng = (float)$coords['x'];
        $lat = (float)$coords['y'];

        $this->Efflintegrationlogger->info("[EfflApiIntegration] Geocoded coords: lat={$lat}, lng={$lng}");

        // 2) Haversine — find nearest dealers
        $collection = $this->dealerCollectionFactory->create();
        $connection = $collection->getConnection();
        $table = $collection->getMainTable();

        // Use Haversine formula (Earth radius in miles = 3959)
        $haversineExpr = "(3959 * acos(
            cos(radians(:lat))
            * cos(radians(`latitude`))
            * cos(radians(`longitude`) - radians(:lng))
            + sin(radians(:lat))
            * sin(radians(`latitude`))
        ))";

        // Build select expression, add distance calculated column, order by distance
        $select = $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                '*',
                'haversine_distance' => new \Zend_Db_Expr($haversineExpr)
            ])
            ->where('is_ffl_active = ?', 1)
            ->order('haversine_distance ASC')
            ->limit(10);

        // Bind params and fetch
        $bind = [':lat' => $lat, ':lng' => $lng];

        try {
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Executing nearest 10 dealers select with params: ' . json_encode($bind));
            $dealers = $connection->fetchAll($select, $bind);
        } catch (\Throwable $e) {
            $this->Efflintegrationlogger->error('[EfflApiIntegration] SQL failed: ' . $e->getMessage());
            $dealers = [];
        }
        foreach ($dealers as &$dealer) {
            $azureDistance = $this->azureMapsService->getDrivingDistanceMiles(
                $lat,
                $lng,
                (float)$dealer['latitude'],
                (float)$dealer['longitude']
            );

            if ($azureDistance === null) {
                $dealerName = $dealer['dealer_name'] ?? ($dealer['name'] ?? 'unknown');

                $this->Efflintegrationlogger->info(
                    "[EfflApiIntegration] Azure route unavailable — falling back to Haversine for dealer: {$dealerName}"
                );
            }

            // If Azure fails (ocean / no route), fallback to Haversine
            $dealer['distance'] = $azureDistance !== null
                ? $azureDistance
                : round((float)$dealer['haversine_distance'], 3);
        }
        usort($dealers, function (array $a, array $b) {
            return ($a['distance'] ?? PHP_INT_MAX) <=> ($b['distance'] ?? PHP_INT_MAX);
        });
        $this->dealers = $dealers;
        $this->noFflDealerAvailable = empty($dealers);

        $this->Efflintegrationlogger->info('[EfflApiIntegration] Nearest dealers found: ' . count($dealers));
        foreach ($dealers as $d) {
            $name = $d['dealer_name'] ?? ($d['name'] ?? 'unknown');
            $distance = isset($d['distance']) ? round((float)$d['distance'], 3) : 'n/a';
            $this->Efflintegrationlogger->info("[EfflApiIntegration] Dealer found: {$name} — distance = {$distance} miles");
        }
    }

    /**
     * Helper: store chosen dealer in component state and persist to quote
     *
     * Called from template via wire:click / wire:click.prevent
     *
     * @param int $selectedId
     * @param array|string $dealerJson  dealer data (template passes JSON-encoded dealer)
     */

    protected function getShippingAddressFingerprint(): string
    {
        $quote = $this->checkoutSession->getQuote();
        $addr = $quote ? $quote->getShippingAddress() : null;
        if (!$addr) {
            return '';
        }

        $parts = [
            trim(str_replace(["\r", "\n"], ' ', (string)$addr->getStreetLine(1))),
            trim((string)$addr->getCity()),
            trim((string)$addr->getRegion()),
            trim((string)$addr->getPostcode()),
            trim((string)$addr->getCountryId()),
        ];

        // lower + single-space separated for stable comparison
        return strtolower(implode('|', array_filter($parts)));
    }

    /**
     * Called when user clicks Select — persist selection to quote and save the shipping-address fingerprint.
     * Only selectedOption is set to true from user action here (so UI shows selected summary).
     */

    public function selectedFflDealer($selectedId)
    {
        // Load dealer directly by ID
        $collection = $this->dealerCollectionFactory->create();
        $dealer = $collection->getItemById((int)$selectedId);

        if (!$dealer) {
            $this->Efflintegrationlogger->warning('[EfflApiIntegration] Dealer not found for id=' . $selectedId);
            $this->dispatchBrowserEvent('ffl:selection:error', ['message' => 'Dealer not found.']);
            return;
        }

        $dealerData = $dealer->getData();

        // Save both entity_id (for UI) and ffl_id (for order persistence)
        $this->selectedFflDealerId = (int)$selectedId;
        $this->addressFfl = $dealerData;
        $this->selectedOption = true;

        $this->Efflintegrationlogger->info('[EfflApiIntegration] Dealer selected (entity_id): ' . $this->selectedFflDealerId);
        $this->Efflintegrationlogger->info('[EfflApiIntegration] Selected dealer data: ' . json_encode($this->truncateForLog($dealerData)));

        try {
            $quote = $this->checkoutSession->getQuote();
            $fflDealerNameAndAddress = $this->formatDealerSummaryForQuote($dealerData);

            // compute and store current shipping address fingerprint
            $addressFingerprint = $this->getShippingAddressFingerprint();

            $this->Efflintegrationlogger->info('[EfflApiIntegration] Saving dealer data to quote: ' . json_encode($this->truncateForLog($dealerData)));
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Saving selected address fingerprint to quote: ' . $addressFingerprint);

            // Save readable summary + ffl_id + fingerprint to quote
            $quote->setData('ffl_dealer', $fflDealerNameAndAddress);

            // IMPORTANT: use ffl_id (license number), not entity_id
            if (!empty($dealerData['ffl_id'])) {
                $quote->setData('selected_ffl_dealer_id', $dealerData['ffl_id']);
            } else {
                // fallback in case ffl_id missing
                $quote->setData('selected_ffl_dealer_id', $this->selectedFflDealerId);
            }

            $quote->setData('selected_address_hash', $addressFingerprint);

            $this->quoteRepository->save($quote);

            $this->Efflintegrationlogger->info('[EfflApiIntegration] Persisted selected FFL dealer to quote (quote_id=' . $quote->getId() . ', ffl_id=' . $quote->getData('selected_ffl_dealer_id') . ').');
            $this->eventManager->dispatch('ahy_fffl_selection_update', ['ahy_ffl_selection' => $this]);

            $this->dispatchBrowserEvent('ffl:selection:success', ['message' => 'FFL selection saved.']);

            // Dispatch event with dealer data for summary

            $this->dispatchBrowserEvent('ffl:dealer:selected', [
                'dealer' => [
                    'name' => $dealerData['dealer_name'] ?? '',
                    'street' => $dealerData['street'] ?? '',
                    'city' => $dealerData['city'] ?? '',
                    'state' => $dealerData['state'] ?? '',
                    'zip_code' => $dealerData['zip_code'] ?? ''
                ]
            ]);
            $this->dispatchBrowserEvent('ffl:selection:success', ['message' => 'FFL selection saved.']);
        } catch (\Throwable $e) {
            $this->Efflintegrationlogger->error('[EfflApiIntegration] Failed to persist selected dealer to quote: ' . $e->getMessage());
            $this->dispatchBrowserEvent('ffl:selection:error', ['message' => 'Failed to save selected FFL dealer.']);
        }
    }
    /**
     * Hydrate selection from quote but:
     *  - do NOT auto-show the selected summary (selectedOption stays false)
     *  - if the saved address hash does not match the current shipping address, clear persisted selection from quote
     */
    protected function hydrateSelectionFromQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote) {
            return;
        }

        $savedSummary = $quote->getData('ffl_dealer');
        $savedId = (int)$quote->getData('selected_ffl_dealer_id') ?: 0;
        $savedHash = (string)$quote->getData('selected_address_hash') ?: '';

        $currentHash = $this->getShippingAddressFingerprint();

        $this->Efflintegrationlogger->info('[EfflApiIntegration] Hydrating selection from quote — savedId=' . $savedId . ' savedHash=' . ($savedHash ? substr($savedHash, 0, 40) . '...' : '(none)') . ' currentHash=' . ($currentHash ? substr($currentHash, 0, 40) . '...' : '(none)'));

        if ($savedId && $savedHash !== '' && $savedHash === $currentHash) {
            // Persisted selection matches current address: keep id but do not mark as "fresh selection"
            $this->selectedFflDealerId = $savedId;
            $this->persistedSelectionMatchesAddress = true;
            // load dealer data to addressFfl for helper methods (but do not set selectedOption true)
            $collection = $this->dealerCollectionFactory->create();
            $dealerModel = $collection->getItemById($savedId);
            if ($dealerModel) {
                $this->addressFfl = $dealerModel->getData();
            } else {
                $this->addressFfl = ['dealer_name' => $savedSummary];
            }
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Hydrated persisted selection (will NOT auto-show) for dealer id=' . $savedId);
            // note: selectedOption remains false (UI will not display selected-summary)
        } elseif ($savedId && $savedHash !== $currentHash) {
            // Saved selection exists but address changed -> clear persisted selection to avoid stale data
            $this->Efflintegrationlogger->info('[EfflApiIntegration] Saved selection exists but address changed — clearing persisted selection (savedId=' . $savedId . ').');
            try {
                $quote->setData('ffl_dealer', null);
                $quote->setData('selected_ffl_dealer_id', null);
                $quote->setData('selected_address_hash', null);
                $this->quoteRepository->save($quote);
                $this->Efflintegrationlogger->info('[EfflApiIntegration] Cleared persisted FFL selection from quote due to address mismatch.');
            } catch (\Throwable $e) {
                $this->Efflintegrationlogger->error('[EfflApiIntegration] Failed to clear persisted selection: ' . $e->getMessage());
            }
            // Reset component state
            $this->selectedFflDealerId = 0;
            $this->addressFfl = null;
            $this->selectedOption = false;
            $this->persistedSelectionMatchesAddress = false;
            $this->agreeOnTermAndCondition = false;
        } else {
            // nothing persisted
            $this->selectedFflDealerId = 0;
            $this->addressFfl = null;
            $this->selectedOption = false;
            $this->persistedSelectionMatchesAddress = false;
        }
    }

    /**
     * Listener callable when Alpine/Magewire emits updateAgreeOnTermAndCondition
     */
    public function updateAgreeOnTermAndCondition($value)
    {
        $this->agreeOnTermAndCondition = (bool)$value;
        $this->Efflintegrationlogger->info('[EfflApiIntegration] Agreement on terms updated: ' . ($this->agreeOnTermAndCondition ? 'true' : 'false'));
    }

    /**
     * Return shipping address object (for template compatibility)
     *
     * @return \Magento\Quote\Model\Quote\Address|null
     */
    public function getAddressList()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote ? $quote->getShippingAddress() : null;
    }

    /* -------------------------
     * Helper accessors used by template
     * ------------------------- */

    public function getSelectedOption()
    {
        return (bool)$this->selectedOption;
    }

    public function getSelectedFflDealerId()
    {
        return (int)$this->selectedFflDealerId;
    }

    public function getSelectedFflDealerName()
    {
        if (is_array($this->addressFfl) && ($this->addressFfl['dealer_name'] ?? null)) {
            return $this->addressFfl['dealer_name'];
        }
        return null;
    }

    public function getSelectedFflDealerAddressHtml()
    {
        if (is_array($this->addressFfl)) {
            $parts = [];
            foreach (['street', 'city', 'state', 'zip_code'] as $k) {
                if (!empty($this->addressFfl[$k])) {
                    $parts[] = $this->addressFfl[$k];
                }
            }
            return implode(', ', $parts);
        }
        return null;
    }

    public function hasSelectedFflDealerAddressHtml()
    {
        $html = $this->getSelectedFflDealerAddressHtml();
        return !empty($html);
    }

    /**
     * If some configuration requires this step to be mandatory, return true.
     * Template expects getIsRequired() — keep it simple for now.
     *
     * @return bool
     */
    public function getIsRequired()
    {
        return false; // change to true if you want to enforce selecting a dealer always
    }

    /**
     * EvaluationInterface implementation — called when checkout tries to proceed
     *
     * Must return EvaluationResultInterface indicating success or error (with custom event name)
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        // No dealers were found for this address
        if ($this->noFflDealerAvailable) {
            $this->Efflintegrationlogger->warning('[EfflApiIntegration] Validation failed: no FFL dealers available for address.');
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage('We did not find any FFL dealer for the provided address. Please recheck and try again.');
        }

        // User hasn't selected a dealer yet
        $hasSelection = $this->selectedOption || $this->persistedSelectionMatchesAddress;

        if (!$hasSelection) {
            $this->Efflintegrationlogger->warning('[EfflApiIntegration] Validation failed: user has not selected an FFL dealer (and no persisted selection matches address).');
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage('TO PROCEED FURTHER SELECT THE FFL DEALER');
        }

        // User hasn't agreed to the acknowledgement terms
        if (!$this->agreeOnTermAndCondition) {
            $this->Efflintegrationlogger->warning('[EfflApiIntegration] Validation failed: user did not acknowledge terms.');
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('ffl:selection:error')
                ->withMessage('PLEASE AGREE TO THE TERMS BEFORE PROCEEDING.');
        }

        // All good
        $this->Efflintegrationlogger->info('[EfflApiIntegration] Validation passed: FFL selection complete.');
        return $resultFactory->createSuccess();
    }

    /* -------------------------
     * Utilities
     * ------------------------- */

    /**
     * Create a readable dealer summary string for storing on quote
     *
     * @param array $dealer
     * @return string
     */
    protected function formatDealerSummaryForQuote(array $dealer): string
    {
        $parts = [];
        if (!empty($dealer['dealer_name'])) {
            $parts[] = $dealer['dealer_name'];
        }
        if (!empty($dealer['street'])) {
            $parts[] = $dealer['street'];
        }
        $cityParts = [];
        if (!empty($dealer['city'])) {
            $cityParts[] = $dealer['city'];
        }
        if (!empty($dealer['state'])) {
            $cityParts[] = $dealer['state'];
        }
        if (!empty($dealer['zip_code'])) {
            $cityParts[] = $dealer['zip_code'];
        }
        if (!empty($cityParts)) {
            $parts[] = implode(', ', $cityParts);
        }
        if (!empty($dealer['phone'])) {
            $parts[] = 'Phone: ' . $dealer['phone'];
        } elseif (!empty($dealer['phone_no'])) {
            $parts[] = 'Phone: ' . $dealer['phone_no'];
        }
        return implode(' | ', $parts);
    }

    /**
     * Truncate large responses for logs (avoid storing huge payloads)
     *
     * @param mixed $data
     * @param int $maxLen
     * @return mixed
     */
    protected function truncateForLog($data, $maxLen = 1000)
    {
        $json = json_encode($data);
        if ($json === false) {
            return $data;
        }
        if (strlen($json) > $maxLen) {
            return substr($json, 0, $maxLen) . '...';
        }
        return $data;
    }
}
