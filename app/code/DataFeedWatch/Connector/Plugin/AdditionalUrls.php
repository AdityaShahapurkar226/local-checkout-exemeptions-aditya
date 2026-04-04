<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

class AdditionalUrls extends ProductUrl
{
    /**
     * @param Product $product
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::URL_REWRITE_TABLE);

        $query = sprintf(
            "SELECT `request_path` FROM `%s` WHERE `entity_id` = '%s' AND `store_id` = %s AND `entity_type` = 'product' AND `metadata` IS NOT NULL",
            $tableName,
            $product->getId(),
            $product->getStoreId()
        );

        $store = $this->storeManager->getStore($product->getStoreId());

        return array_map(function ($path) use ($store) { return rtrim($store->getUrl($path), '/'); }, $connection->fetchCol($query));
    }

    /**
     * @return string
     */
    protected function getDataVar(): string
    {
        return 'AdditionalUrls';
    }
}