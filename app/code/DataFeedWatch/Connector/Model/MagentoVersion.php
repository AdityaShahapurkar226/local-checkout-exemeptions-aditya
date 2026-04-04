<?php
/**
 * Created by Q-Solutions Studio
 * Date: 09.12.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Model;

use DataFeedWatch\Connector\Api\MagentoVersionInterface;
use Magento\Framework\App\ProductMetadataInterface;

class MagentoVersion implements MagentoVersionInterface
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @method __construct
     * @param  ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @method getVersionData
     * @return array
     */
    public function getVersionData() : array
    {
        $versionData = [];
        $versionData['name'] = $this->productMetadata->getName();
        $versionData['version'] = $this->productMetadata->getVersion();
        $versionData['edition'] = $this->productMetadata->getEdition();

        return [$versionData];
    }
}
