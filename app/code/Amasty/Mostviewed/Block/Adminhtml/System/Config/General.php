<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class General extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return sprintf(
            '<div class="comment">%s<a target="_blank" href="%s">%s</a></div>',
            __('To configure the rules please go to Catalog -> Amasty Related Products -> '),
            $this->getUrl('amasty_mostviewed/product_group/'),
            __('Related Product Rules')
        );
    }
}
