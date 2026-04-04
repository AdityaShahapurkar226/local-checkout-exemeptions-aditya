<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;

class AddOrchidStatuses implements DataPatchInterface
{
    private StatusFactory $statusFactory;
    private StatusResource $statusResource;

    public function __construct(
        StatusFactory $statusFactory,
        StatusResource $statusResource
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResource = $statusResource;
    }

    public function apply(): void
    {
        $statuses = [
            'orchid_fail_restriction' => 'Orchid Fail Restriction',
            'orchid_restricted'       => 'Orchid Restricted',
            'roster_restricted'       => 'Roster Restricted',
            'partially_on_hold'       => 'Partially On Hold',
            'compliance_hold'         => 'Compliance Hold'
        ];

        $stateMapping = [
            'orchid_fail_restriction' => ['holded', 'processing'],
            'orchid_restricted'       => ['holded', 'processing'],
            'roster_restricted'       => ['holded', 'processing'],
            'partially_on_hold'       => ['holded','processing'],
            'compliance_hold'         => ['holded','processing']
        ];

        foreach ($statuses as $code => $label) {
            $status = $this->statusFactory->create();
            $status->setData('status', $code)
                   ->setData('label', $label);

            try {
                $this->statusResource->save($status);

                // Assign to state(s)
                if (isset($stateMapping[$code])) {
                    foreach ($stateMapping[$code] as $state) {
                        $status->assignState($state, false, true);
                    }
                }
            } catch (\Exception $e) {
                // Skip if already exists
            }
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}