<?php

namespace Ahy\DeleteElasticsearch\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DriverInterface;

class DeleteIndices
{
    private DeploymentConfig $deploymentConfig;
    private Curl $curl;
    private LoggerInterface $logger;
    private DriverInterface $fileDriver;
    private ScopeConfigInterface $scopeConfig;
    private string $logFile;
    private $responseBody;
    private $statusCode;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        Curl $curl,
        LoggerInterface $logger,
        DriverInterface $fileDriver,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
        $this->scopeConfig = $scopeConfig;
        $this->logFile = BP . '/var/log/elasticsearch_delete.log';
    }

    public function execute()
    {
        try {
            [$host, $port] = $this->getElasticsearchConfig();

            if (!$host || !$port) {
                $this->logger->error('Elasticsearch host or port is missing.');
                return;
            }

            // Normalize localhost to 127.0.0.1
            if ($host === 'localhost') {
                $host = '127.0.0.1';
            }

            $url = "$host:$port/_all";

            // Set DELETE request
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->addHeader("Content-Type", "application/json");
            $this->deleteElasticsearchIndices($url); // Corrected to DELETE
            $response = $this->responseBody;
            $httpCode = $this->statusCode;
            if ($httpCode >= 200 && $httpCode < 300 && isset($response['acknowledged']) && $response['acknowledged']) {
                // $this->logger->info('Elasticsearch indices successfully deleted.');
                // $this->logger->info("Response Body: " . json_encode($response, JSON_PRETTY_PRINT));
            } else {
                $this->logger->error('Failed to delete indices: ' . json_encode($response, JSON_PRETTY_PRINT));
            };
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    private function deleteElasticsearchIndices(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->responseBody = json_decode($response, true);
        $this->statusCode = $httpCode;
        return $this->responseBody ?? [];

    }

    private function getElasticsearchConfig(): array
    {
        // Fetch from env.php first
        $config = $this->deploymentConfig->get('elasticsearch')
            ?? $this->deploymentConfig->get('search_engine')
            ?? $this->deploymentConfig->get('catalog/search/elasticsearch7');

        $host = $config['elasticsearch7_server_hostname'] ?? $config['host'] ?? null;
        $port = $config['elasticsearch7_server_port'] ?? $config['port'] ?? null;

        if (!$host || !$port) {
            // $this->logger->info('Fetching Elasticsearch config from core_config_data.');
            $host = $this->scopeConfig->getValue('catalog/search/elasticsearch7_server_hostname', ScopeInterface::SCOPE_STORE);
            $port = $this->scopeConfig->getValue('catalog/search/elasticsearch7_server_port', ScopeInterface::SCOPE_STORE);
        }

        return [$host, $port];
    }

    private function handleException(\Exception $e): void
    {
        $errorMessage = "Error deleting Elasticsearch indices: " . $e->getMessage();
        $this->logger->error($errorMessage);
        $this->logToFile($errorMessage);
    }

    private function logToFile(string $message): void
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $this->fileDriver->filePutContents($this->logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            $this->logger->error("Failed to write to log file: " . $e->getMessage());
        }
    }
}
