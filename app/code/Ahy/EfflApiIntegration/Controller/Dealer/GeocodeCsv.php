<?php

namespace Ahy\EfflApiIntegration\Controller\Dealer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ahy\EfflApiIntegration\Service\CensusGeocodingService;
use Ahy\EfflApiIntegration\Model\OrchidFflDealerFactory;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer as OrchidFflDealerResource;
use Magento\Framework\Filesystem\Io\File;

class GeocodeCsv extends Action
{
    protected $resultJsonFactory;
    protected $censusService;
    protected $dealerFactory;
    protected $dealerResource;
    protected $fileIo;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CensusGeocodingService $censusService,
        OrchidFflDealerFactory $dealerFactory,
        OrchidFflDealerResource $dealerResource,
        File $fileIo
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->censusService = $censusService;
        $this->dealerFactory = $dealerFactory;
        $this->dealerResource = $dealerResource;
        $this->fileIo = $fileIo;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $fileName = $this->getRequest()->getParam('file');
        if (!$fileName) {
            return $resultJson->setData(['error' => 'Missing "file" parameter']);
        }

        $dirPath = '/home2/dev1/www//var/log/dealer-import-csvs/';
        $filePath = $dirPath . basename($fileName);
        if (!file_exists($filePath)) {
            return $resultJson->setData(['error' => 'CSV file not found: ' . $filePath]);
        }

        $failedCsv = $dirPath . 'geocode_failed_' . basename($fileName);
        $handle = fopen($filePath, 'r');
        $failedHandle = fopen($failedCsv, 'w');

        $headers = fgetcsv($handle); // read CSV headers
        if ($headers && $failedHandle) {
            fputcsv($failedHandle, $headers); // write headers to failed CSV
        }

        $processed = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $processed++;
            $data = array_combine($headers, $row);

            $params = [
                'street' => $data['street'] ?? '',
                'city'   => $data['city'] ?? '',
                'state'  => $data['state'] ?? '',
                'zip'    => $data['zip_code'] ?? ''
            ];

            $geoResponse = $this->censusService->geocodeAddress($params);
            $lat = $geoResponse['result']['addressMatches'][0]['coordinates']['y'] ?? null;
            $long = $geoResponse['result']['addressMatches'][0]['coordinates']['x'] ?? null;

            // if no lat/long, log to failed CSV and skip
            if (empty($lat) || empty($long)) {
                fputcsv($failedHandle, $row);
                $skipped++;
                continue;
            }

            $dealerData = [
                'ffl_id'              => $data['ffl_id'] ?? '',
                'dealer_name'         => $data['dealer_name'] ?? '',
                'ffl_expiration_date' => $data['ffl_expiration_date'] ?? null,
                'street'              => $data['street'] ?? '',
                'city'                => $data['city'] ?? '',
                'state'               => $data['state'] ?? '',
                'zip_code'            => $data['zip_code'] ?? '',
                'latitude'            => $lat,
                'longitude'           => $long,
                'is_ffl_active'       => 1
            ];

            try {
                $model = $this->dealerFactory->create();
                $this->dealerResource->load($model, $dealerData['ffl_id'], 'ffl_id');
                $model->setData($dealerData);
                $this->dealerResource->save($model);
                $updated++;
            } catch (\Exception $e) {
                // on DB error, also log to failed CSV
                fputcsv($failedHandle, $row);
                $skipped++;
            }
        }

        fclose($handle);
        fclose($failedHandle);

        return $resultJson->setData([
            'success' => true,
            'file_processed' => $filePath,
            'records_processed' => $processed,
            'records_saved' => $updated,
            'records_skipped' => $skipped,
            'failed_csv' => $failedCsv
        ]);
    }
}
