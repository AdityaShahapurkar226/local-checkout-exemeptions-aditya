<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\MegaMenu\Model\Menu;

use Amasty\MegaMenuLite\Model\Menu\TreeResolver;
use Amasty\Mostviewed\Model\OptionSource\TopMenuLink;
use Amasty\Mostviewed\Plugin\Theme\Block\Html\Topmenu;

class TreeResolverPlugin extends Topmenu
{
    /**
     * @param TreeResolver $treeResolver
     * @param array $additionalLinks
     * @return array
     */
    public function afterGetBeforeAdditionalLinks(TreeResolver $treeResolver, array $additionalLinks)
    {
        if ($this->getTopMenuEnabled() === TopMenuLink::DISPLAY_FIRST) {
            $additionalLinks = $this->populateAdditionalLinks($additionalLinks);
        }

        return $additionalLinks;
    }

    /**
     * @param TreeResolver $treeResolver
     * @param array $additionalLinks
     * @return array
     */
    public function afterGetAdditionalLinks(TreeResolver $treeResolver, array $additionalLinks)
    {
        if ($this->getTopMenuEnabled() === TopMenuLink::DISPLAY_LAST) {
            $additionalLinks = $this->populateAdditionalLinks($additionalLinks);
        }

        return $additionalLinks;
    }

    /**
     * @param array $additionalLinks
     * @return array
     */
    private function populateAdditionalLinks(array $additionalLinks)
    {
        $bundlesNodeAsArray = $this->_getNodeAsArray();

        if ($bundlesNodeAsArray['url'] !== '') {
            array_push($additionalLinks, $bundlesNodeAsArray);
        }

        return $additionalLinks;
    }
}
