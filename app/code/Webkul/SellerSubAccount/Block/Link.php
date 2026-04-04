<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Block;

use Magento\Framework\View\Element\Html\Link\Current;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(\Webkul\Marketplace\Helper\Data::class);
        if (!$helper->isSeller()) {
            return '';
        }
        return parent::_toHtml();
    }
    
    /**
     * Return the current url of recently viewed page
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
