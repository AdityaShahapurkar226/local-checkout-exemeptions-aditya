<?php

namespace Ahy\EfflApiIntegration\Service;

use Ahy\EfflApiIntegration\Model\OrchidFflDealerFactory;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer as DealerResource;
use Ahy\EfflApiIntegration\Service\CensusGeocodingService;
use Ahy\EfflApiIntegration\Logger\Logger;

class DealerUpdateService
{
    protected $dealerFactory;
    protected $dealerResource;
    protected $censusService;
    protected $logger;

    protected $inserted = 0;
    protected $updated  = 0;
    protected $deleted  = 0;
    protected $skipped  = 0;

    public function __construct(
        OrchidFflDealerFactory $dealerFactory,
        DealerResource $dealerResource,
        CensusGeocodingService $censusService,
        Logger $logger
    ) {
        $this->dealerFactory  = $dealerFactory;
        $this->dealerResource = $dealerResource;
        $this->censusService  = $censusService;
        $this->logger         = $logger;
    }

    public function processDealer(array $dealerData, string $action)
    {
        $fflId = $dealerData['ffl_id'] ?? $dealerData['fflId'] ?? null;
        $dealer = $this->dealerFactory->create();
        $this->dealerResource->load($dealer, $fflId, 'ffl_id');

        // Normalize Orchid ffl_current ("True"/"False") to int 1/0
        $fflCurrentRaw = $dealerData['ffl_current'] ?? $dealerData['fflCurrent'] ?? null;
        $fflCurrent    = ($fflCurrentRaw === 'True') ? 1 : 0;

        switch (strtoupper($action)) {
            case 'INSERT':
                $existingDealer = $this->dealerFactory->create();
                $this->dealerResource->load($existingDealer, $fflId, 'ffl_id');

                if ($existingDealer->getId()) {
                    // Update existing dealer
                    $existingDealer->setData($dealerData);
                    $existingDealer->setData('ffl_current', $fflCurrent);
                    $existingDealer->setIsFflActive($fflCurrent);
                    $this->dealerResource->save($existingDealer);
                    $this->updated++;
                    $this->logger->info("Dealer {$fflId} updated/reactivated (INSERT)");
                    $this->geocodeDealer($existingDealer);
                } else {
                    // New dealer
                    $dealer->setData($dealerData);
                    $dealer->setData('ffl_current', $fflCurrent);
                    $dealer->setIsFflActive($fflCurrent);
                    $this->dealerResource->save($dealer);
                    $this->inserted++;
                    $this->logger->info("Dealer {$fflId} inserted");
                    $this->geocodeDealer($dealer);
                }
                break;

            case 'UPDATE':
                if ($dealer->getId()) {
                    $shouldSave = false;
                    $oldData = $dealer->getData();
                    $oldAddress = trim($dealer->getStreet() . $dealer->getCity() . $dealer->getState() . $dealer->getZipCode());
                    $newAddress = trim($dealerData['street'] . $dealerData['city'] . $dealerData['state'] . $dealerData['zip_code']);

                    foreach ($dealerData as $field => $value) {
                        if ($dealer->getData($field) != $value) {
                            $dealer->setData($field, $value);
                            $shouldSave = true;
                        }
                    }

                    // Sync ffl_current and is_ffl_active
                    if ($dealer->getData('ffl_current') != $fflCurrent) {
                        $dealer->setData('ffl_current', $fflCurrent);
                        $shouldSave = true;
                    }
                    if ($dealer->getIsFflActive() != $fflCurrent) {
                        $dealer->setIsFflActive($fflCurrent);
                        $shouldSave = true;
                    }

                    $addressChanged = ($oldAddress !== $newAddress);

                    if ($shouldSave) {
                        $this->dealerResource->save($dealer);
                        $this->updated++;
                        $this->logger->info(
                            "Dealer {$fflId} updated",
                            ['before' => $oldData, 'after' => $dealer->getData()]
                        );

                        if ($addressChanged) {
                            $this->geocodeDealer($dealer);
                        }
                    } else {
                        $this->skipped++;
                        $this->logger->info("Dealer {$fflId} skipped (no changes)");
                    }
                } else {
                    $this->logger->warning("Dealer {$fflId} not found for UPDATE");
                }
                break;

            case 'DELETE':
                if ($dealer->getId()) {
                    if ($fflCurrent === 1) {
                        $dealer->setData('ffl_current', 1);
                        $dealer->setIsFflActive(0);
                        $this->logger->info("Dealer {$fflId} marked inactive (DELETE but ffl_current kept TRUE)");
                    } else {
                        $dealer->setData('ffl_current', 0);
                        $dealer->setIsFflActive(0);
                        $this->logger->info("Dealer {$fflId} marked inactive (DELETE)");
                    }

                    $this->dealerResource->save($dealer);
                    $this->deleted++;
                } else {
                    $this->logger->warning("Dealer {$fflId} not found for DELETE");
                }
                break;


            default:
                $this->logger->warning("Unknown dealer action: {$action}", $dealerData);
        }
    }

    protected function geocodeDealer($dealer)
    {
        try {
            $params = [
                'street' => $dealer->getStreet(),
                'city'   => $dealer->getCity(),
                'state'  => $dealer->getState(),
                'zip'    => $dealer->getZipCode()
            ];
            $geoResponse = $this->censusService->geocodeAddress($params);

            $lat  = $geoResponse['result']['addressMatches'][0]['coordinates']['y'] ?? null;
            $long = $geoResponse['result']['addressMatches'][0]['coordinates']['x'] ?? null;

            if (!empty($lat) && !empty($long)) {
                $dealer->setLatitude($lat);
                $dealer->setLongitude($long);
                $this->dealerResource->save($dealer);
                $this->logger->info("Dealer {$dealer->getFflId()} geocoded: {$lat}, {$long}");
            } else {
                $this->logger->warning("Geocode failed: No coordinates for {$dealer->getFflId()}");
            }
        } catch (\Exception $e) {
            $this->logger->error("Geocode failed for dealer {$dealer->getFflId()}: " . $e->getMessage());
        }
    }
}
