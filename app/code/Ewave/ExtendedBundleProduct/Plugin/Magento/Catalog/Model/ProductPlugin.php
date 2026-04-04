<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Catalog\Model;

use Magento\Catalog\Model\Product as Subject;

/**
 * Class ProductPlugin
 */
class ProductPlugin
{
    const BUNDLE_IDENTITY_OPTION = 'bundle_identity';

    /**
     * @param Subject $subject
     * @param string $code
     * @param mixed $value
     * @param null $product
     * @return array
     */
    public function beforeAddCustomOption(
        Subject $subject,
        $code,
        $value,
        $product = null
    ) {
        if ($code == self::BUNDLE_IDENTITY_OPTION) {
            $valueExploded = explode('_', $value);
            if (empty($valueExploded) || (count($valueExploded) - 1) % 2 !== 0) {
                return [$code, $value, $product];
            }

            /** @var \Magento\Catalog\Model\Product\Configuration\Item\Option $buyRequest */
            if (!($buyRequest = $subject->getCustomOption('info_buyRequest'))) {
                return [$code, $value, $product];
            }

            $buyRequestValue = json_decode($buyRequest->getValue(), true);
            $superAttributes = $buyRequestValue['bundle_super_attribute'] ?? [];

            for ($i = 1; $i < count($valueExploded) - 1; $i += 2) {
                $selectionId = $valueExploded[$i];
                $simpleIds = $superAttributes[$selectionId] ?? [];
                if (!empty($simpleIds)) {
                    $value .= '_' . implode('_', $simpleIds);
                }
            }
        }

        return [$code, $value, $product];
    }
}
