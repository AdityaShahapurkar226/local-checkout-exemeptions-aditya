<?php
namespace Ahy\EfflApiIntegration\Controller\Dealer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\Io\File;

class ExportCsv extends Action
{
    protected $resultJsonFactory;
    protected $curl;
    protected $file;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Curl $curl,
        File $file
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->curl = $curl;
        $this->file = $file;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $pageNumber = (int)($this->getRequest()->getParam('pageNumber') ?? 1);
        $pageSize   = (int)($this->getRequest()->getParam('pageSize') ?? 100);

        $url = "https://app.fflbizhub.com/api/fflMasterSearch/ezcheck?pageNumber={$pageNumber}&pageSize={$pageSize}";

        try {
            // Call BizHub API
            $this->curl->setHeaders([
                'X-API-Auth-Token' => '6a471226-d6cd-421d-9f54-da2e13eb784d',
                'Accept' => 'application/json'
            ]);
            $this->curl->get($url);
            $response = $this->curl->getBody();

            $dealers = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($dealers)) {
                return $resultJson->setData([
                    'error' => 'Invalid API response',
                    'raw_response' => $response
                ]);
            }

            // CSV Path
            $dirPath = '/home2/dev1/www/var/log/dealer-import-csvs/';
            $fileName = "dealers-data-{$pageNumber}-{$pageSize}.csv";
            $filePath = $dirPath . $fileName;

            if (!is_dir($dirPath)) {
                $this->file->mkdir($dirPath, 0755);
            }

            // Open CSV using SplFileObject for memory efficiency
            $csvFile = new \SplFileObject($filePath, 'w');

            // Define headers
            $headers = [
                'ffl_id',
                'dealer_name',
                'ffl_expiration_date',
                'fflCurrent',
                'street',
                'city',
                'state',
                'zip_code',
                'latitude',
                'longitude',
                'is_ffl_active'
            ];

            $csvFile->fputcsv($headers);

            $totalRecords = 0;

            foreach ($dealers as $dealer) {
                $expirationDate = '';
                if (!empty($dealer['fflExpirationDate'])) {
                    $dateObj = \DateTime::createFromFormat('F Y', $dealer['fflExpirationDate']);
                    if ($dateObj) {
                        $expirationDate = $dateObj->format('Y-m-01'); // First day of month
                    }
                }

                $row = [
                    $dealer['fflId'] ?? '',
                    $dealer['businessName'] ?? '',
                    $expirationDate,
                    $dealer['fflCurrent'] ?? '',
                    $dealer['premiseStreet'] ?? '',
                    $dealer['premiseCity'] ?? '',
                    $dealer['premiseState'] ?? '',
                    $dealer['premiseZipCode'] ?? '',
                    '', // latitude
                    '', // longitude
                    1   // is_ffl_active
                ];

                $csvFile->fputcsv($row);
                $totalRecords++;
            }

            return $resultJson->setData([
                'success' => true,
                'message' => 'CSV file generated successfully.',
                'file' => $filePath,
                'total_records' => $totalRecords
            ]);

        } catch (\Exception $e) {
            return $resultJson->setData(['error' => $e->getMessage()]);
        }
    }
}
