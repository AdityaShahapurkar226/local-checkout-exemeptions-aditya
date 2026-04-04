<?php
/**
 * Created by Q-Solutions Studio
 * Date: 01.07.19
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Maciej Buchert <maciej@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ParentIds
 * @package DataFeedWatch\Connector\Plugin
 */
class ParentIds extends ExtensionAttributeAbstract
{
    const RELATIONS_TABLE = "catalog_product_relation";

    /**
     * @param Product $product
     * @return Product
     */
    protected function setExtensionAttribute(Product $product): Product
    {
        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            return parent::setExtensionAttribute($product);
        }
        return $product;
    }

    /**
     * @param Product $product
     * @return array|mixed
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::RELATIONS_TABLE);

        $query = $this->resourceConnection
            ->getConnection()
            ->select()
            ->from($tableName, 'parent_id')
            ->where(sprintf('child_id = %s', $product->getId()));

        return $connection->fetchCol($query);
    }

    /**
     * @return string
     */
    protected function getDataVar(): string
    {
        return 'ParentIds';
    }
}
