<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Extensions\ConditionalDiscounts;

use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command\RemoveByPackIdInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command\SaveInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var SaveInterface
     */
    private $saveCommand;

    /**
     * @var RemoveByPackIdInterface
     */
    private $removeByPackId;

    public function __construct(SaveInterface $saveCommand, RemoveByPackIdInterface $removeByPackId)
    {
        $this->saveCommand = $saveCommand;
        $this->removeByPackId = $removeByPackId;
    }

    /**
     * @param Pack|object $entity
     * @param array $arguments
     * @return Pack|bool|object|void
     * @throws CouldNotSaveException
     * @throws CouldNotDeleteException
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getDiscountType() === DiscountType::CONDITIONAL) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $discounts = $extensionAttributes->getConditionalDiscounts() ?: [];
            foreach ($discounts as $discount) {
                $discount->setPackId((int) $entity->getId());
                $this->saveCommand->execute($discount);
            }
        } else {
            $this->removeByPackId->execute((int) $entity->getId());
        }

        return $entity;
    }
}
