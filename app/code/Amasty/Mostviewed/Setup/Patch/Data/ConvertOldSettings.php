<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\OptionSource\ReplaceType;
use Amasty\Mostviewed\Model\OptionSource\SourceType;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection;
use Amasty\Mostviewed\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConvertOldSettings implements DataPatchInterface
{
    public const SECTION_PATH = 'ammostviewed';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        CollectionFactory $groupCollectionFactory,
        GroupFactory $groupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return ConvertOldSettings
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function apply()
    {
        if ($this->isGroupsNotExist() && $oldSettings = $this->getOldSettings()) {
            $oldSettings = $this->divideDataByType($oldSettings);
            foreach ($oldSettings as $groupName => $groupData) {
                if (isset($groupData['enabled']) && $groupData['enabled'] != '1') {
                    continue;
                }

                $data = $this->getDefaultDataByType($groupName);
                if (isset($groupData['replace']) && $groupData['replace'] == '0') {
                    if (isset($groupData['size'])) {
                        $data[GroupInterface::MAX_PRODUCTS] = $groupData['size'];
                    }
                    // do nothing if display manually added only
                    $groupData = [];
                }

                foreach ($groupData as $key => $value) {
                    switch ($key) {
                        case 'size':
                            $data[GroupInterface::MAX_PRODUCTS] = $value;
                            break;
                        case 'replace':
                            $value = ($value == 1) ? ReplaceType::REPLACE : ReplaceType::ADD;
                            $data[GroupInterface::REPLACE_TYPE] = $value;
                            break;
                        case 'in_stock':
                            $data[GroupInterface::SHOW_OUT_OF_STOCK] = !$value;
                            break;
                        case 'out_of_stock_only':
                            $data[GroupInterface::SHOW_FOR_OUT_OF_STOCK] = $value ? 1 : 0;
                            break;
                        case 'data_source':
                            switch ($value) {
                                case '0': //SOURCE_VIEWED
                                    $data[GroupInterface::SOURCE_TYPE] = SourceType::SOURCE_VIEWED;
                                    break;
                                case '1': //SOURCE_BOUGHT
                                    $data[GroupInterface::SOURCE_TYPE] = SourceType::SOURCE_BOUGHT;
                                    break;
                                case '2':
                                    if (isset($groupData['category_condition'])) {
                                        $groupData['category_condition'] = null;
                                    }
                                    if (isset($groupData['brand_attribute'])) {
                                        $groupData['brand_attribute'] = null;
                                    }
                                    if (isset($groupData['price_condition'])) {
                                        $groupData['price_condition'] = null;
                                    }
                                    if (isset($groupData['condition_id']) && $groupData['condition_id']) {
                                        $data[GroupInterface::SOURCE_TYPE] = $value;
                                        $data[GroupInterface::CONDITIONS] = $this->getOldCondition(
                                            (int) $groupData['condition_id']
                                        );
                                    }
                                    break;
                            }
                            break;
                        case 'category_condition':
                            if (!$value) {
                                continue 2;
                            }
                            $value = 'category_ids';
                            break;
                        case 'brand_attribute':
                            if (!$value) {
                                continue 2;
                            }
                            $data[GroupInterface::SAME_AS] = 1;
                            $data[GroupInterface::SAME_AS_CONDITIONS] = <<<CONDITIONS
{
"type":"Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition\\\\Combine",
"attribute":null,
"operator":null,
"value":1,
"is_value_processed":null,
"aggregator":all,
"conditions": [{
"type":"Amasty\\\\Mostviewed\\\\Model\\\\Rule\\\\Condition\\\\Product",
"attribute":"$value",
"operator":"==",
"value":false,
"is_value_processed":false
}]
}
CONDITIONS;
                            break;
                        case 'price_condition':
                            switch ($value) {
                                case '0':
                                    $value = false;
                                    break;
                                case '1':
                                    $value = '==';
                                    break;
                                case '2':
                                    $value = '>';
                                    break;
                                case '3':
                                    $value = '<';
                                    break;
                            }
                            if (!$value) {
                                continue 2;
                            }

                            $data[GroupInterface::SAME_AS] = 1;
                            $data[GroupInterface::SAME_AS_CONDITIONS] = <<<CONDITIONS
{
"type":"Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition\\\\Combine",
"attribute":null,
"operator":null,
"value":"1",
"is_value_processed":null,
"aggregator":"all",
"conditions": [{
"type":"Amasty\\\\Mostviewed\\\\Model\\\\Rule\\\\Condition\\\\Price",
"attribute":false,
"operator":"$value",
"value":false,
"is_value_processed":false
}]
}'
CONDITIONS;
                            break;
                    }
                }

                if ($data) {
                    $this->createGroup($data);
                }
            }
        }

        return $this;
    }

    private function getOldSettings(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $select = $connection->select()
            ->from($tableName)
            ->where('path like ?', sprintf('%s%%', self::SECTION_PATH));

        return $connection->fetchAll($select);
    }

    private function isGroupsNotExist(): bool
    {
        /** @var Collection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();
        return !$groupCollection->getSize();
    }

    private function divideDataByType(array $oldSettings): array
    {
        $result = [
            'related_products' => [],
            'cross_sells'      => [],
            'up_sells'         => []
        ];

        foreach ($oldSettings as $setting) {
            $name = $setting['path'];
            foreach ($result as $key => $item) {
                if (strpos($name, $key) !== false) {
                    $name = str_replace($key . '/', '', $name);
                    $name = str_replace(self::SECTION_PATH . '/', '', $name);
                    $result[$key][$name] = $setting['value'];
                    break;
                }
            }
        }

        return $result;
    }

    private function getDefaultDataByType(string $groupName): array
    {
        $data = [];
        switch ($groupName) {
            case 'related_products':
                $data['name'] = __('Related Products');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::PRODUCT_INTO_RELATED;
                break;
            case 'cross_sells':
                $data['name'] = __('Cross-Sells');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::CART_INTO_CROSSSEL;
                break;
            case 'up_sells':
                $data['name'] = __('Up-Sells');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::PRODUCT_INTO_UPSELL;
                break;
        }

        $data[GroupInterface::STORES] = '0';
        $data[GroupInterface::CUSTOMER_GROUP_IDS] = '0,1,2,3';

        return $data;
    }

    private function getOldCondition(int $conditionId): string
    {
        $result = '';
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('amasty_mostviewed_rule');
        if ($connection->isTableExists($tableName)) {
            $select = $connection->select()
                ->from($tableName, ['conditions_serialized'])
                ->where('rule_id=?', $conditionId);

            $result = $connection->fetchRow($select);
            $result = isset($result['conditions_serialized']) ? $result['conditions_serialized'] : '';
        }

        return $result;
    }

    private function createGroup(array $data): void
    {
        $group = $this->groupFactory->create();
        $group->addData($data);
        $group->save();
    }
}
