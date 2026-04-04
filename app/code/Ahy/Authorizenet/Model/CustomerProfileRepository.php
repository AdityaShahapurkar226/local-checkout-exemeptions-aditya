<?php

namespace Ahy\Authorizenet\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CustomerProfileRepository
{
    protected ResourceConnection $resource;
    protected LoggerInterface $logger;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger   = $logger;
    }

    public function saveProfileMapping(int $customerId, string $customerProfileId): void
    {
        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName('ahy_authorizenet_customer_profile');

        try {
            // Check if mapping already exists
            $select = $connection->select()
                ->from($tableName)
                ->where('customer_id = ?', $customerId);

            $existing = $connection->fetchRow($select);

            if ($existing) {
                $this->logger->info("CustomerProfile already exists for customer_id {$customerId}");
                return;
            }

            $connection->insert($tableName, [
                'customer_id'         => $customerId,
                'customer_profile_id' => $customerProfileId,
                'created_at'          => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            $this->logger->info("Saved customer_profile_id: {$customerProfileId} for customer_id: {$customerId}");
        } catch (\Exception $e) {
            $this->logger->error('Error saving customer profile mapping: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to save Authorize.Net customer profile.'));
        }
    }

    public function hasProfile(int $customerId): bool
    {
        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName('ahy_authorizenet_customer_profile');

        $select = $connection->select()
            ->from($tableName, ['entity_id'])
            ->where('customer_id = ?', $customerId);

        return (bool) $connection->fetchOne($select);
    }

    public function getProfileIdByCustomerId(int $customerId): ?string
    {
        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName('ahy_authorizenet_customer_profile');

        try {
            $select = $connection->select()
                ->from($tableName, ['customer_profile_id'])
                ->where('customer_id = ?', $customerId)
                ->limit(1);

            $result = $connection->fetchOne($select);
            return $result ?: null;
        } catch (\Exception $e) {
            $this->logger->error("Error fetching customerProfileId for customer_id {$customerId}: " . $e->getMessage());
            return null;
        }
    }
}
