<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\ResourceModel\Option;

use Magento\Bundle\Model\ResourceModel\Option\Collection as Subject;

/**
 * Class CollectionPlugin
 */
class CollectionPlugin
{
    /**
     * @param Subject $subject
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $stripBefore
     * @param bool $appendAll
     * @return array
     */
    public function beforeAppendSelections(
        Subject $subject,
        $selectionsCollection,
        $stripBefore = false,
        $appendAll = true
    ) {
        foreach ($selectionsCollection->getItems() as $selection) {
            $selection->setRequiredOptions(0);
        }
        return [$selectionsCollection, $stripBefore, $appendAll];
    }
}
