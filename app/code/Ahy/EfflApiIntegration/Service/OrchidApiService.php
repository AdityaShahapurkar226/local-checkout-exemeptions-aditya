<?php
namespace Ahy\EfflApiIntegration\Service;

use Magento\Framework\HTTP\Client\Curl;

class OrchidApiService
{
    protected $curl;

    const ORCHID_API_URL = "https://app.fflbizhub.com/api/fflMasterSearch/ezcheck";
    const ORCHID_API_KEY = "6a471226-d6cd-421d-9f54-da2e13eb784d"; // move to env.php/config

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Fetch FFL Dealers from Orchid API for a specific page and page size
     *
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    public function getAllFflDealers(int $pageNumber = 1, int $pageSize = 10): array
    {
        return $this->fetchDealersFromApi($pageNumber, $pageSize);
    }

    /**
     * Fetch dealers for a specific page
     *
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    protected function fetchDealersFromApi(int $pageNumber, int $pageSize): array
    {
        $url = self::ORCHID_API_URL
            . "?pageNumber={$pageNumber}&pageSize={$pageSize}";

        try {
            $this->curl->setHeaders([
                "Content-Type" => "application/json",
                "X-API-Auth-Token" => self::ORCHID_API_KEY
            ]);

            $this->curl->get($url);
            $response = $this->curl->getBody();

            return json_decode($response, true) ?: [];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
