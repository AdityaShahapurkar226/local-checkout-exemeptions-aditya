<?php

namespace Ewave\ExtendedBundleProduct\Observer\Adminhtml;

use Ewave\ExtendedBundleProduct\Helper\Data as Helper;
use Ewave\ExtendedBundleProduct\Model\BundleLinkRegistry;
use Magento\Bundle\Model\Selection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BundleSelectionSaveBefore implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var BundleLinkRegistry
     */
    private $bundleLinkRegistry;

    /**
     * @param Helper $helper
     * @param BundleLinkRegistry $bundleLinkRegistry
     */
    public function __construct(
        Helper $helper,
        BundleLinkRegistry $bundleLinkRegistry
    ) {
        $this->helper = $helper;
        $this->bundleLinkRegistry = $bundleLinkRegistry;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $productLink = $this->bundleLinkRegistry->getLink();
        if (null !== $productLink && is_array($productLink->getConfigurableOptions())) {
            /** @var Selection $entity */
            $entity = $observer->getEvent()->getEntity();
            $this->helper->setConfigurableOptions($entity, $productLink->getConfigurableOptions());
        }
        return $this;
    }
}
