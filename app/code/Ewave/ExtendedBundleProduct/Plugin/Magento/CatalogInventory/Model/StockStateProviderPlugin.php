<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider as Subject;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class StockStateProviderPlugin
 */
class StockStateProviderPlugin
{
    /**
     * @param Subject $subject
     * @param StockItemInterface $stockItem
     * @param int $qty
     * @return array
     */
    public function beforeCheckQty(
        Subject $subject,
        StockItemInterface $stockItem,
        $qty
    ) {
        if ($stockItem->getTypeId() == Configurable::TYPE_CODE) {
            $stockItem->setUseConfigManageStock(false);
            $stockItem->setManageStock(false);
        }
        return [$stockItem, $qty];
    }
}
