<?php
namespace Ahy\EfflApiIntegration\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\DeploymentConfig;

class DealerProcessService
{
    protected $curl;
    protected $file;
    protected $dealerUpdateService;
    protected $logger;
    protected $deploymentConfig;

    public function __construct(
        Curl $curl,
        File $file,
        DealerUpdateService $dealerUpdateService,
        LoggerInterface $logger,
        DeploymentConfig $deploymentConfig
    ) {
        $this->curl = $curl;
        $this->file = $file;
        $this->dealerUpdateService = $dealerUpdateService;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function run($pageNumber = 1, $pageSize = 1000)
    {
        $this->logger->info("Dealer process started", [
            'pageNumber' => $pageNumber,
            'pageSize' => $pageSize
        ]);

        $config = $this->deploymentConfig->get('orchid_api_effl');
        $apiKey = $config['API_KEY'] ?? '';
        $baseUrl = $config['FFL_API_URL'] ?? '';

        if (!$apiKey || !$baseUrl) {
            $this->logger->error("API key or URL missing in env.php");
            throw new \Exception("API key or URL missing in env.php");
        }

        $url = "{$baseUrl}?pageNumber={$pageNumber}&pageSize={$pageSize}";
        $this->curl->setHeaders([
            'X-API-Auth-Token' => $apiKey,
            'Accept' => 'application/json'
        ]);
        $this->curl->get($url);

        $changes = json_decode($this->curl->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($changes)) {
            $this->logger->error("Invalid API response", ['response' => $this->curl->getBody()]);
            return ['error' => 'Invalid API response'];
        }

        $processedData = [];
        $errors = [];
        $processed = 0;

        foreach ($changes as $change) {
            $action = strtoupper($change['action'] ?? '');
            $dealerData = [
                'ffl_id' => $change['fflId'] ?? '',
                'dealer_name' => $change['businessName'] ?? '',
                'ffl_expiration_date' => $change['fflExpirationDate'] ?? null,
                'ffl_current' => $change['fflCurrent'] ?? '',
                'street' => $change['premiseStreet'] ?? '',
                'city' => $change['premiseCity'] ?? '',
                'state' => $change['premiseState'] ?? '',
                'zip_code' => $change['premiseZipCode'] ?? '',
                'action' => $action
            ];

            try {
                $this->dealerUpdateService->processDealer($dealerData, $action);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = ['ffl_id' => $dealerData['ffl_id'], 'error' => $e->getMessage()];
            }

            $processedData[] = $dealerData;
        }

        // Save CSV logs
        $today = date('Y-m-d');
        $dirPath = BP . "/var/log/dealer-update-csvs/{$today}/";
        $fileName = "dealer-changes-page{$pageNumber}-{$pageSize}.csv";
        $filePath = $dirPath . $fileName;

        if (!is_dir($dirPath)) {
            $this->file->mkdir($dirPath, 0755);
        }

        $fp = fopen($filePath, 'w');
        if (!empty($processedData)) fputcsv($fp, array_keys($processedData[0]));
        foreach ($processedData as $row) fputcsv($fp, $row);
        fclose($fp);

        return [
            'success' => true,
            'processed' => $processed,
            'errors' => $errors,
            'file' => $filePath
        ];
    }
}
