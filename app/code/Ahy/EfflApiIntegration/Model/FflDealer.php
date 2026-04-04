<?php

namespace Ahy\EfflApiIntegration\Model;

use Ahy\EfflApiIntegration\Api\FflDealerInterface;
use Ahy\EfflApiIntegration\Service\OrchidApiService;
use Ahy\EfflApiIntegration\Service\CensusGeocodingService;
use Ahy\EfflApiIntegration\Logger\Logger as EfflLogger;
use Ahy\EfflApiIntegration\Model\OrchidFflDealerFactory;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer as OrchidFflDealerResource;

class FflDealer implements FflDealerInterface
{
    protected $orchidService;
    protected $censusService;
    protected $Efflintegrationlogger;
    protected $dealerFactory;
    protected $dealerResource;

    public function __construct(
        OrchidApiService $orchidService,
        CensusGeocodingService $censusService,
        EfflLogger $Efflintegrationlogger,
        OrchidFflDealerFactory $dealerFactory,
        OrchidFflDealerResource $dealerResource
    ) {
        $this->orchidService = $orchidService;
        $this->censusService = $censusService;
        $this->Efflintegrationlogger = $Efflintegrationlogger;
        $this->dealerFactory = $dealerFactory;
        $this->dealerResource = $dealerResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getDealerWithGeo()
    {
        $finalData = [];
        $pageNumber = 1; // you can change this to fetch a different page
        $pageSize = 10;  // number of dealers per request
        $totalFetched = 0;

        // fetch dealers from Orchid with dynamic pageNumber and pageSize
        $dealers = $this->orchidService->getAllFflDealers($pageNumber, $pageSize);

        if (isset($dealers['error'])) {
            return ['error' => $dealers['error']];
        }

        foreach ($dealers as $dealer) {

            $params = [
                'street' => $dealer['premiseStreet'] ?? '',
                'city'   => $dealer['premiseCity'] ?? '',
                'state'  => $dealer['premiseState'] ?? '',
                'zip'    => $dealer['premiseZipCode'] ?? ''
            ];

            $geoResponse = $this->censusService->geocodeAddress($params);
            $lat = $geoResponse['result']['addressMatches'][0]['coordinates']['y'] ?? null;
            $long = $geoResponse['result']['addressMatches'][0]['coordinates']['x'] ?? null;

            $dealerData = [
                'ffl_id'              => $dealer['fflId'] ?? '',
                'dealer_name'         => $dealer['businessName'] ?? '',
                'ffl_expiration_date' => $dealer['fflExpirationDate'] ?? null,
                'street'              => $dealer['premiseStreet'] ?? '',
                'city'                => $dealer['premiseCity'] ?? '',
                'state'               => $dealer['premiseState'] ?? '',
                'zip_code'            => $dealer['premiseZipCode'] ?? '',
                'latitude'            => $lat,
                'longitude'           => $long,
                'is_ffl_active'       => 1
            ];

            if (!empty($dealerData['ffl_expiration_date'])) {
                $dateObj = \DateTime::createFromFormat('F Y', $dealerData['ffl_expiration_date']);
                if ($dateObj) {
                    $lastDay = $dateObj->format('Y-m-t');
                    $dealerData['ffl_expiration_date'] = $lastDay . ' 23:59:59';
                } else {
                    $dealerData['ffl_expiration_date'] = null;
                }
            }

            try {
                $model = $this->dealerFactory->create();
                $this->dealerResource->load($model, $dealerData['ffl_id'], 'ffl_id');
                $model->setData($dealerData);
                $this->dealerResource->save($model);
                $this->Efflintegrationlogger->info("[EfflApiIntegration] Saved dealer FFL ID {$dealerData['ffl_id']} into DB.");
            } catch (\Exception $e) {
                $this->Efflintegrationlogger->error("[EfflApiIntegration] DB Save failed for FFL ID {$dealerData['ffl_id']}: " . $e->getMessage());
            }

            $finalData[] = $dealerData;
            $totalFetched++;
        }

        $this->Efflintegrationlogger->info('[EfflApiIntegration] Total dealers saved: ' . count($finalData));

        return $finalData;
    }
}
