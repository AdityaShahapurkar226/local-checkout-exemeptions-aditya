<?php

namespace Ahy\EverestNews\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;

class UpdateNews
{
    protected LoggerInterface $logger;
    protected UrlInterface $urlBuilder;

    public function __construct(LoggerInterface $logger, UrlInterface $urlBuilder)
    {
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        $magePubDirPath = BP . '/pub';
        $baseUrl = $this->urlBuilder->getBaseUrl();
        $url = $baseUrl . 'everestdigest/wp-admin/admin-ajax.php?action=ahy_latest_posts_json';
        $filePath = $magePubDirPath . '/everest_news_latest_posts.json';

        try {
            if (!file_exists($filePath)) {
                touch($filePath);
                chmod($filePath, 0644);
            }

            $response = file_get_contents($url);

            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data !== null) {
                    file_put_contents($filePath, $response);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating Everest news JSON: ' . $e->getMessage());
        }
    }
}
