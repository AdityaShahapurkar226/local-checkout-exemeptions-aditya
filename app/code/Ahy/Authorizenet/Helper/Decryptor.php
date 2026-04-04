<?php
namespace Ahy\Authorizenet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;

class Decryptor extends AbstractHelper
{
    const METHOD = 'aes-256-cbc';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Decrypt an encrypted string with the given key
     */
    public function decrypt($encryptedString, $key)
    {
        try {
            $this->logger->info('[Decryptor] Starting decryption process');

            $key = substr(hash('sha256', $key), 0, 32);
            $this->logger->info('[Decryptor] Generated key hash: ' . substr($key, 0, 8) . '...');

            $data = base64_decode($encryptedString);
            if ($data === false || strlen($data) < 17) {
                $this->logger->error('[Decryptor] Invalid encrypted data received');
                return false;
            }

            $iv = substr($data, 0, 16);
            $encryptedData = substr($data, 16);

            $this->logger->info('[Decryptor] IV length: ' . strlen($iv));
            $this->logger->info('[Decryptor] Encrypted payload length: ' . strlen($encryptedData));

            $decrypted = openssl_decrypt($encryptedData, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

            $this->logger->info('[Decryptor] Decryption result: ' . ($decrypted ? 'success' : 'failed'));

            return $decrypted;
        } catch (\Exception $e) {
            $this->logger->error('[Decryptor] Exception: ' . $e->getMessage());
            return false;
        }
    }
}