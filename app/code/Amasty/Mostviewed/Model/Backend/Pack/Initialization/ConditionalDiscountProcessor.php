<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount\ColumnValidatorInterface;
use Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount\ValidatorInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query\GetByIdInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query\GetNewInterface;
use Amasty\Mostviewed\Ui\DataProvider\Pack\Form\Modifier\ConditionalDiscounts;
use Magento\Framework\Exception\LocalizedException;

class ConditionalDiscountProcessor implements ProcessorInterface
{
    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var array
     */
    private $columnValidators;

    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    public function __construct(
        GetByIdInterface $getById,
        GetNewInterface $getNew,
        array $columnValidators = [],
        array $validators = []
    ) {
        $this->getById = $getById;
        $this->getNew = $getNew;
        $this->columnValidators = $columnValidators;
        $this->validators = $validators;
    }

    /**
     * @param PackInterface $pack
     * @param array $inputPackData
     * @return void
     * @throws LocalizedException
     */
    public function execute(PackInterface $pack, array $inputPackData): void
    {
        if ($pack->getDiscountType() === DiscountType::CONDITIONAL) {
            $discounts = [];
            $newDiscounts = [];

            $discountsData = $this->retrieveDiscountsData($inputPackData);
            foreach ($discountsData as $discountData) {
                $discountId = $discountData[ConditionalDiscountInterface::ID];
                if ($discountId) {
                    /** @var ConditionalDiscountInterface|ConditionalDiscount $discount */
                    $discount = $this->getById->execute((int) $discountId);
                } else {
                    $discount = $this->getNew->execute();
                }
                $discount->addData($discountData);
                $discounts[] = $discount;
                $newDiscounts[] = $discount->getId();
            }

            $oldDiscounts = $pack->getExtensionAttributes()->getConditionalDiscounts() ?: [];
            // update discounts array with deleted ranges
            foreach ($oldDiscounts as $discount) {
                if (!in_array($discount->getId(), $newDiscounts)) {
                    $discount->isDeleted(true);
                    $discounts[] = $discount;
                }
            }

            $pack->getExtensionAttributes()->setConditionalDiscounts($discounts);
        }
    }

    /**
     * @param array $inputData
     * @return array
     * @throws LocalizedException
     */
    private function retrieveDiscountsData(array $inputData): array
    {
        $discountsData = [];

        if (!empty($inputData[ConditionalDiscounts::GRID_CONDITIONAL])) {
            $inputData = $inputData[ConditionalDiscounts::GRID_CONDITIONAL];

            foreach ($inputData as $inputDiscountData) {
                $this->validateDiscountData($inputDiscountData);

                $discountData[ConditionalDiscountInterface::ID]
                    = $inputDiscountData[ConditionalDiscountInterface::ID] ?? null;
                $discountData[ConditionalDiscountInterface::ID]
                    = $discountData[ConditionalDiscountInterface::ID] ?: null;
                $discountData[ConditionalDiscountInterface::NUMBER_ITEMS]
                    = (int) $inputDiscountData[ConditionalDiscountInterface::NUMBER_ITEMS];
                $discountData[ConditionalDiscountInterface::DISCOUNT_AMOUNT]
                    = (float) $inputDiscountData[ConditionalDiscountInterface::DISCOUNT_AMOUNT];

                $discountsData[] = $discountData;
            }

            $this->validateAllData($discountsData);
        }

        return $discountsData;
    }

    /**
     * @param array $discountData
     * @return void
     * @throws LocalizedException
     */
    private function validateDiscountData(array $discountData): void
    {
        foreach ($this->columnValidators as $columnCode => $columnValidatorData) {
            $columnName = $columnValidatorData['column_name'];
            /** @var ColumnValidatorInterface[] $columnValidators */
            $columnValidators = $columnValidatorData['validators'];

            $columnValue = $discountData[$columnCode] ?? null;
            foreach ($columnValidators as $columnValidator) {
                $columnValidator->validate($columnName, $columnValue);
            }
        }
    }

    /**
     * @param array $discountsData
     * @return void
     * @throws LocalizedException
     */
    private function validateAllData(array $discountsData): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($discountsData);
        }
    }
}
