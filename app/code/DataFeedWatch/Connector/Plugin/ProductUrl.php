<?php
/**
 * Created by Q-Solutions Studio
 * Date: 17.10.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductUrl extends ExtensionAttributeAbstract
{
    const URL_REWRITE_TABLE = 'url_rewrite';
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ParentIds constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($extensionFactory, $resourceConnection);
        $this->storeManager = $storeManager;
    }

    /**
     * @param Product $product
     * @return Product
     */
    protected function setExtensionAttribute(Product $product): Product
    {
        if ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
            return parent::setExtensionAttribute($product);
        }
        return $product;
    }

    /**
     * @param Product $product
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::URL_REWRITE_TABLE);

        $query = sprintf(
            "SELECT `request_path` FROM `%s` WHERE `entity_id` = '%s' AND `store_id` = %s AND `entity_type` = 'product' AND `metadata` IS NULL",
            $tableName,
            $product->getId(),
            $product->getStoreId()
        );

        $productPath = $connection->fetchOne($query);

        return $productPath ? rtrim($this->storeManager->getStore($product->getStoreId())->getUrl($productPath), '/') : '';;
    }

    protected function getDataVar(): string
    {
        return 'ProductUrl';
    }
}
