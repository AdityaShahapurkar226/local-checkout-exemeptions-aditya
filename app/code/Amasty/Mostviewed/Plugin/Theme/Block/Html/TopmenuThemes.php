<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Theme\Block\Html;

use Amasty\Mostviewed\Model\OptionSource\TopMenuLink;

class TopmenuThemes extends Topmenu
{
    /**
     * @param $subject
     * @param $html
     * @return string
     */
    public function afterRenderCategoriesMenuHtml(
        $subject,
        $html
    ) {
        $position = $this->config->getModuleConfig('bundle_packs/top_menu_enabled');

        if ($position) {
            $htmlMenu = $this->generateHtml($this->_getNodeAsArray());
            if ($position == TopMenuLink::DISPLAY_FIRST) {
                $html = $htmlMenu . $html;
            } else {
                $html .= $htmlMenu;
            }
        }

        return $html;
    }

    /**
     * @param $subject
     * @param $html
     * @return string
     */
    public function afterGetMegamenuHtml(
        $subject,
        $html
    ) {
        return $this->afterRenderCategoriesMenuHtml($subject, $html);
    }

    /**
     * @param $data
     * @return string
     */
    private function generateHtml($data)
    {
        return '<li class="nav-item nav-item--brand level0 level-top">
                    <a class="level-top" href="' . $data['url'] . '"><span>' . $data['name'] . '</span></a>
                </li>';
    }
}
