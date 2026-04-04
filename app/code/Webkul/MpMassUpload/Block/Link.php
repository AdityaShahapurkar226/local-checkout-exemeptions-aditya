<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Block;

use Magento\Framework\View\Element\Html\Link\Current;
use Webkul\Marketplace\Helper\Data;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
            parent::__construct($context, $defaultPath, $data);
            $this->_mpHelper = $mpHelper;
    }
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_mpHelper->isSeller()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Return Current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * Return marketplace helper
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return  $this->_mpHelper;
    }
}
