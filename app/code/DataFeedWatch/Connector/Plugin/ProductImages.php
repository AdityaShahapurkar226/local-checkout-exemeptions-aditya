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
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection;

class ProductImages extends ExtensionAttributeAbstract
{
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
     * @return array|mixed
     */
    protected function getExtensionData(Product $product)
    {
        $images = $product->getMediaGalleryImages();
        $imagesData = [];

        if ($images instanceof Collection) {
            foreach ($images as $image) {
                $imagesData[] = $image->getData('url');
            }
        }

        return $imagesData;
    }

    protected function getDataVar(): string
    {
        return 'ProductImages';
    }
}
