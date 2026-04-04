<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\ConfigurableProduct\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Subject;
use Magento\Framework\DataObject;

/**
 * Class ConfigurablePlugin
 */
class ConfigurablePlugin
{
    /**
     * @param Subject $subject
     * @param DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string|null $processMode
     * @return array
     */
    public function beforePrepareForCartAdvanced(
        Subject $subject,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        $selectionId = $product->getSelectionId();
        $superAttributes = (array)$buyRequest->getBundleSuperAttribute();
        if ($selectionId && isset($superAttributes[$selectionId])) {
            $buyRequest->setSuperAttribute($superAttributes[$selectionId]);
        }
        return [$buyRequest, $product, $processMode];
    }
}
