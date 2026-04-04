<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization;

use Amasty\Base\Model\Serializer;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Magento\Framework\Exception\LocalizedException;

class PackProcessor implements ProcessorInterface
{
    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(Serializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param PackInterface|Pack $pack
     * @param array $inputPackData
     * @return void
     * @throws LocalizedException
     */
    public function execute(PackInterface $pack, array $inputPackData): void
    {
        $pack->addData($this->prepareData($inputPackData));
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function prepareData(array $data): array
    {
        if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
            $data['customer_group_ids'] = implode(',', $data['customer_group_ids']);
        }

        if (isset($data['product_ids']['child_products_container'])) {
            $childs = [];
            $childsInformation = [];
            foreach ($data['product_ids']['child_products_container'] as $product) {
                $childs[(int)$product['position']] = $product['entity_id'];
                if (isset($data['discount_type'])
                    && $data['discount_type'] == DiscountType::PERCENTAGE
                    && ((float) $product['discount_amount'] < 0 || (float) $product['discount_amount'] > 100)
                ) {
                    throw new LocalizedException(
                        __('Invalid value provided for the Discount Amount field.
                        Please enter a valid value between 0 and 100')
                    );
                }
                $childsInformation[$product['entity_id']] = [
                    'entity_id' => $product['entity_id'],
                    'quantity' => $product['quantity'],
                    'discount_amount' => $product['discount_amount']
                ];
            }
            ksort($childs);
            $data['product_ids'] = implode(',', $childs);
            $data[PackInterface::PRODUCTS_INFO] = $this->jsonSerializer->serialize($childsInformation);
            unset($data['child_products_container']);
        } else {
            $data['product_ids'] = '';
        }

        if (!$data['pack_id']) {
            unset($data['pack_id']);
        }

        if (isset($data['parent_products_container'])) {
            $childs = [];
            foreach ($data['parent_products_container'] as $product) {
                $childs[] = $product['entity_id'];
            }
            $data['parent_product_ids'] = $childs;
            unset($data['parent_products_container']);
        }

        if (isset($data['discount_type'])
            && isset($data['discount_amount'])
            && $data['discount_type'] == DiscountType::PERCENTAGE
            && ((float)$data['discount_amount'] < 0 || (float)$data['discount_amount'] > 100)
        ) {
            throw new LocalizedException(
                __('Invalid value provided for the Discount Amount field. Please enter a valid value between 0 and 100')
            );
        }
        if (isset($data['discount_amount'])) {
            $data['discount_amount'] = str_replace(',', '.', $data['discount_amount']);
            $data['discount_amount'] = (float)$data['discount_amount'];
        }

        return $data;
    }
}
