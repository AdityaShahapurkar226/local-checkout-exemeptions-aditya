<?php

namespace Ahy\EfflApiIntegration\Controller\Dealer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\Io\File;
use Ahy\EfflApiIntegration\Service\DealerUpdateService;
use Ahy\EfflApiIntegration\Logger\Logger;
use Magento\Framework\App\DeploymentConfig;

class ProcessChanges extends Action
{
    protected $resultJsonFactory;
    protected $curl;
    protected $file;
    protected $dealerUpdateService;
    protected $logger;
    protected $deploymentConfig;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Curl $curl,
        File $file,
        DealerUpdateService $dealerUpdateService,
        Logger $logger,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($context);
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->curl                = $curl;
        $this->file                = $file;
        $this->dealerUpdateService = $dealerUpdateService;
        $this->logger              = $logger;
        $this->deploymentConfig    = $deploymentConfig;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $pageNumber = (int)($this->getRequest()->getParam('pageNumber') ?? 1);
        $pageSize   = (int)($this->getRequest()->getParam('pageSize') ?? 1000);

        $this->logger->info("Dealer process started", [
            'pageNumber' => $pageNumber,
            'pageSize'   => $pageSize
        ]);

        try {
            // Fetch from env.php
            $config   = $this->deploymentConfig->get('orchid_api_effl');
            $apiKey   = $config['API_KEY'] ?? '';
            $baseUrl  = $config['FFL_API_URL'] ?? '';

            if (!$apiKey || !$baseUrl) {
                $this->logger->error("API key or URL missing in env.php");
                throw new \Exception('API key or URL is missing in env.php');
            }

            $url = "{$baseUrl}?pageNumber={$pageNumber}&pageSize={$pageSize}";
            $this->curl->setHeaders([
                'X-API-Auth-Token' => $apiKey,
                'Accept'           => 'application/json'
            ]);
            $this->curl->get($url);
            $response = $this->curl->getBody();

            // Decode API response
            $changes = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($changes)) {
                $this->logger->error("Invalid API response", ['response' => $response]);
                return $resultJson->setData([
                    'error'        => 'Invalid API response',
                    'raw_response' => $response
                ]);
            }
            $processed     = 0;
            $errors        = [];
            $processedData = [];

            foreach ($changes as $change) {
                $action = strtoupper($change['action'] ?? '');
                $dealerData = [
                    'ffl_id'              => $change['fflId'] ?? '',
                    'dealer_name'         => $change['businessName'] ?? '',
                    'ffl_expiration_date' => $change['fflExpirationDate'] ?? null,
                    'ffl_current'         =>$change['fflCurrent'] ?? '',
                    'street'              => $change['premiseStreet'] ?? '',
                    'city'                => $change['premiseCity'] ?? '',
                    'state'               => $change['premiseState'] ?? '',
                    'zip_code'            => $change['premiseZipCode'] ?? '',
                    'action'              => $action
                ];

                try {
                    $this->dealerUpdateService->processDealer($dealerData, $action);
                    $this->logger->info("Dealer processed", [
                        'ffl_id' => $dealerData['ffl_id'],
                        'action' => $action
                    ]);
                    $processed++;
                } catch (\Exception $e) {
                    $this->logger->info("Dealer processing failed", [
                        'ffl_id' => $dealerData['ffl_id'],
                        'error'  => $e->getMessage()
                    ]);
                    $errors[] = [
                        'ffl_id' => $dealerData['ffl_id'],
                        'error'  => $e->getMessage()
                    ];
                }

                $processedData[] = $dealerData;
            }

            // --- Save only in dealer-import-csvs folder, date-wise ---
            $today    = date('Y-m-d');
            $dirPath  = BP . "/var/log/dealer-update-csvs/{$today}/";
            $fileName = "dealer-changes-page{$pageNumber}-{$pageSize}.csv";
            $filePath = $dirPath . $fileName;

            if (!is_dir($dirPath)) {
                $this->file->mkdir($dirPath, 0755);
                $this->logger->info("Created log directory", ['path' => $dirPath]);
            }

            $fp = fopen($filePath, 'w');
            if (!empty($processedData)) {
                fputcsv($fp, array_keys($processedData[0])); // headers
            }
            foreach ($processedData as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            $this->logger->info("CSV log saved", ['file' => $filePath]);

            return $resultJson->setData([
                'success'        => true,
                'message'        => "Processed {$processed} dealer changes",
                'total_records'  => count($changes),
                'processed_file' => $filePath,
                'errors'         => $errors
            ]);
        } catch (\Exception $e) {
            $this->logger->info("Fatal error in dealer process", [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);
            return $resultJson->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}
