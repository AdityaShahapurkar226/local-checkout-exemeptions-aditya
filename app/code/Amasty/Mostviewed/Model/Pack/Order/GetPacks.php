<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Order;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\LoadPackData;
use Magento\Framework\Exception\NoSuchEntityException;

class GetPacks
{
    /**
     * @var LoadPackData
     */
    private $loadPackData;

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var OrderPackFactory
     */
    private $orderPackFactory;

    public function __construct(
        LoadPackData $loadPackData,
        PackRepositoryInterface $packRepository,
        OrderPackFactory $orderPackFactory
    ) {
        $this->loadPackData = $loadPackData;
        $this->packRepository = $packRepository;
        $this->orderPackFactory = $orderPackFactory;
    }

    /**
     * @param int $orderId
     * @return OrderPack[]
     */
    public function execute(int $orderId): array
    {
        $orderPacks = [];

        $packsData = $this->loadPackData->execute($orderId);

        foreach ($packsData as $packId => $packData) {
            /** @var OrderPack $orderPack */
            $orderPack = $this->orderPackFactory->create();
            $orderPack->setQty((int) $packData[PackHistoryTable::QTY_COLUMN]);
            $orderPack->setPackName($packData[PackHistoryTable::PACK_NAME_COLUMN]);
            try {
                $pack = $this->packRepository->getById($packId);
            } catch (NoSuchEntityException $e) {
                $pack = null;
            }
            $orderPack->setPack($pack);

            $orderPacks[] = $orderPack;
        }

        return $orderPacks;
    }
}
