<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Model;

use Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface;
use Webkul\MarketplaceBaseShipping\Model\ResourceModel\ShippingSetting as ResourceShippingSetting;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Customer data model
 *
 */
class ShippingSetting extends \Webkul\MarketplaceBaseShipping\Model\ShippingSetting\AbstractShippingSetting
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MarketplaceBaseShipping\Model\ResourceModel\ShippingSetting::class);
    }
}
