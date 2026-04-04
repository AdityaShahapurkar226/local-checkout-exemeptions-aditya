<?php

namespace Ahy\Authorizenet\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class DeactivateExpiredVaultCards
{
    private $resource;
    private $json;
    private $logger;

    private $stateTimezones = [
        // Eastern Time (ET)
        'CT' => 'America/New_York', 'DE' => 'America/New_York', 'FL' => 'America/New_York',
        'GA' => 'America/New_York', 'IN' => 'America/Indiana/Indianapolis', 'KY' => 'America/New_York',
        'ME' => 'America/New_York', 'MD' => 'America/New_York', 'MA' => 'America/New_York',
        'MI' => 'America/Detroit', 'NH' => 'America/New_York', 'NJ' => 'America/New_York',
        'NY' => 'America/New_York', 'NC' => 'America/New_York', 'OH' => 'America/New_York',
        'PA' => 'America/New_York', 'RI' => 'America/New_York', 'SC' => 'America/New_York',
        'VT' => 'America/New_York', 'VA' => 'America/New_York', 'WV' => 'America/New_York',
        'DC' => 'America/New_York',

        // Central Time (CT)
        'AL' => 'America/Chicago', 'AR' => 'America/Chicago', 'IA' => 'America/Chicago',
        'IL' => 'America/Chicago', 'KS' => 'America/Chicago', 'LA' => 'America/Chicago',
        'MN' => 'America/Chicago', 'MS' => 'America/Chicago', 'MO' => 'America/Chicago',
        'ND' => 'America/Chicago', 'NE' => 'America/Chicago', 'OK' => 'America/Chicago',
        'SD' => 'America/Chicago', 'TN' => 'America/Chicago', 'TX' => 'America/Chicago',
        'WI' => 'America/Chicago',

        // Mountain Time (MT)
        'AZ' => 'America/Phoenix', 'CO' => 'America/Denver', 'ID' => 'America/Boise',
        'MT' => 'America/Denver', 'NM' => 'America/Denver', 'UT' => 'America/Denver',
        'WY' => 'America/Denver',

        // Pacific Time (PT)
        'CA' => 'America/Los_Angeles', 'NV' => 'America/Los_Angeles',
        'OR' => 'America/Los_Angeles', 'WA' => 'America/Los_Angeles',

        // Alaska & Hawaii
        'AK' => 'America/Anchorage', 'HI' => 'Pacific/Honolulu',
    ];

    public function __construct(
        ResourceConnection $resource,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Hourly execution on the 1st — timezone-aware deactivation
     */
    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $tokenTable = $this->resource->getTableName('vault_payment_token');
        $addressTable = $this->resource->getTableName('customer_address_entity');
        $customerTable = $this->resource->getTableName('customer_entity');

        $select = $connection->select()
            ->from(['t' => $tokenTable])
            ->joinLeft(['c' => $customerTable], 't.customer_id = c.entity_id', [])
            ->joinLeft(['a' => $addressTable], 'a.entity_id = c.default_billing', ['region'])
            ->where('t.is_active = ?', 1);

        $tokens = $connection->fetchAll($select);
        $utcNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        foreach ($tokens as $token) {
            $details = $this->json->unserialize($token['details'] ?? '{}');
            $tokenId = $token['entity_id'];

            $expDate = null;
            if (!empty($details['expirationDate'])) {
                $expDate = \DateTime::createFromFormat('m/Y', trim($details['expirationDate']));
            }
            if (!$expDate) {
                $this->logger->warning("Skipping token {$tokenId}: Invalid or missing expirationDate.");
                continue;
            }

            if ($expDate < $utcNow) {
                $regionCode = strtoupper($token['region'] ?? '');

                if (!$regionCode || !isset($this->stateTimezones[$regionCode])) {
                    $this->deactivateToken($connection, $tokenTable, $tokenId);
                    $this->logger->info("Token {$tokenId} deactivated immediately due to missing/unknown region.");
                    continue;
                }

                $tz = new \DateTimeZone($this->stateTimezones[$regionCode]);
                $localNow = $utcNow->setTimezone($tz);
                $localMidnight = new \DateTimeImmutable(
                    $localNow->format('Y-m-01 00:00:00'),
                    $tz
                );

                if ($localNow >= $localMidnight) {
                    $this->deactivateToken($connection, $tokenTable, $tokenId);
                    $this->logger->info("Token {$tokenId} deactivated for region {$regionCode} after local midnight.");
                }
            }
        }
    }

    /**
     * Final sweep — deactivate all expired tokens regardless of timezone.
     * Run this at 10:00 UTC on the 1st of every month.
     */
    public function finalSweep(): void
    {
        $connection = $this->resource->getConnection();
        $tokenTable = $this->resource->getTableName('vault_payment_token');

        $this->logger->info("Final sweep: Checking for any remaining expired tokens...");

        $select = $connection->select()
            ->from($tokenTable, ['entity_id', 'details'])
            ->where('is_active = ?', 1);

        $tokens = $connection->fetchAll($select);
        $utcNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        foreach ($tokens as $token) {
            $details = $this->json->unserialize($token['details'] ?? '{}');
            $tokenId = $token['entity_id'];

            $expDate = null;
            if (!empty($details['expirationDate'])) {
                $expDate = \DateTime::createFromFormat('m/Y', trim($details['expirationDate']));
            }

            if ($expDate && $expDate < $utcNow) {
                $this->deactivateToken($connection, $tokenTable, $tokenId);
                $this->logger->info("Final sweep: Token {$tokenId} deactivated (expired, missed earlier).");
            }
        }
    }

    private function deactivateToken($connection, $tokenTable, $tokenId): void
    {
        $connection->update(
            $tokenTable,
            ['is_active' => 0],
            ['entity_id = ?' => $tokenId]
        );
    }
}