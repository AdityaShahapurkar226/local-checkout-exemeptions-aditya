<?php

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\CityInterface;
use Ahy\EstateApiIntegration\Service\LocalLawApiService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class City implements CityInterface
{
    private $productRepository;
    private $api;
    private $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LocalLawApiService $api,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->api = $api;
        $this->logger = $logger;
    }

    public function getCities($ruleType, $state)
    {
        try {
            $this->logger->info('[City API] START', [
                'rule_type' => $ruleType,
                'state' => $state
            ]);

            if (empty($ruleType) || empty($state)) {
                $this->logger->warning('[City API] Missing Params', [
                    'rule_type' => $ruleType,
                    'state' => $state
                ]);
                return [];
            }

            $cities = $this->api->getAvailableCities(
                $ruleType,
                $state
            );

            $this->logger->info('[City API] Cities Before Sort', [
                'cities' => $cities
            ]);

            // Move "other city" to last
            usort($cities, function ($a, $b) {
                if ($a === 'other city') return 1;
                if ($b === 'other city') return -1;
                return strcmp($a, $b);
            });

            $this->logger->info('[City API] FINAL Cities', [
                'cities' => $cities
            ]);

            return $cities;
        } catch (\Exception $e) {
            $this->logger->error('[City API ERROR]', [
                'message' => $e->getMessage(),
                'rule_type' => $ruleType,
                'state' => $state,
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
    }
}
