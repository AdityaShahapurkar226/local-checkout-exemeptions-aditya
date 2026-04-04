<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;

/**
 * Class CatalogPrice
 * @package DataFeedWatch\Connector\Plugin
 */
class CatalogPrice extends ExtensionAttributeAbstract
{
    const CUSTOMER_CREATE_ACCOUNT_DEFAULT_GROUP = 'customer/create_account/default_group';
    const CATALOGRULE_PRICE_TABLE = 'catalogrule_product_price';
    const CUSTOMER_GROUP_GENERAL = 1;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var State
     */
    protected $state;

    /**
     * CatalogPrice constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     * @param State $state
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager,
        State $state
    ) {
        parent::__construct($extensionFactory, $resourceConnection);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * @param Product $product
     * @return Product
     */
    protected function setExtensionAttribute(Product $product): Product
    {
        try {
            if ($this->state->getAreaCode() == Area::AREA_WEBAPI_REST) {
                return parent::setExtensionAttribute($product);
            } else {
                return $product;
            }
        } catch (LocalizedException $e) {
            return $product;
        }
    }

    /**
     * @param Product $product
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getExtensionData(Product $product)
    {
        $currentTime = date('Y-m-d');

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(self::CATALOGRULE_PRICE_TABLE);
        $cond = <<<SQL
product_id = :product_id AND customer_group_id = :customer_group_id AND website_id = :website_id
AND latest_start_date <= :current_time AND (earliest_end_date IS NULL OR earliest_end_date > :current_time)
SQL;

        $sql = $connection->select()->from($table, ['rule_price'])
            ->where($cond);

        return $connection->fetchOne($sql, [
            'product_id' => $product->getId(),
            'customer_group_id' => $this->getDefaultCustomerGroupId($product),
            'current_time' => $currentTime,
            'website_id' => $this->storeManager->getStore($product->getStoreId())->getWebsiteId()
        ]);
    }

    /**
     * @return string
     */
    protected function getDataVar(): string
    {
        return 'CatalogPrice';
    }

    protected function getDefaultCustomerGroupId(Product $product): int
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_CREATE_ACCOUNT_DEFAULT_GROUP,
            ScopeInterface::SCOPE_STORE,
            $product->getStoreId()
        ) ?? self::CUSTOMER_GROUP_GENERAL;
    }
}