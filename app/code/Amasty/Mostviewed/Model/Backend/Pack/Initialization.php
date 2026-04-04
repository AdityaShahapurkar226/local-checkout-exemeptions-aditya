<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Backend\Pack\Initialization\ProcessorInterface;
use Amasty\Mostviewed\Model\Pack;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Initialization
{
    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var Registry
     */
    private $packRegistry;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(
        PackRepositoryInterface $packRepository,
        Registry $packRegistry,
        array $processors = []
    ) {
        $this->packRepository = $packRepository;
        $this->packRegistry = $packRegistry;
        $this->processors = $processors;
    }

    /**
     * @param int $packId
     * @param array $inputPackData
     * @return PackInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(int $packId, array $inputPackData): PackInterface
    {
        if ($packId) {
            /** @var PackInterface|Pack $pack */
            $pack = $this->packRepository->getById($packId, true);
        } else {
            $pack = $this->packRepository->getNew();
        }

        $this->packRegistry->set($pack);
        foreach ($this->processors as $processor) {
            $processor->execute($pack, $inputPackData);
        }

        return $pack;
    }
}
