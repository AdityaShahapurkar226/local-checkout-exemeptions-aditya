<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\MpMultiShipping\Helper\Data;

class Dashboard implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpMultiShipping\Helper\Data
     */
    protected $helper;
    
    /**
     * @param Http $request
     * @param UrlInterface $url
     * @param MarketplaceHelper $mpHelper
     * @param Data $helper
     */
    public function __construct(
        Http $request,
        UrlInterface $url,
        MarketplaceHelper $mpHelper,
        Data $helper
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
    }

    /**
     * Check if MultiShipping Configuration enabled or not.
     *
     * @return boolean
     */
    public function isMultiShippingActive()
    {
        return $this->helper->getConfigData('active');
    }

    /**
     * Check if menu link is allowed for seller dashboard
     *
     * @return boolean
     */
    public function isAllowedAction()
    {
        $isSeller = $this->mpHelper->isSeller();
        $isSellerGroup = $this->mpHelper->isSellerGroupModuleInstalled();
        $active = $this->helper->getConfigData('active');

        if ($isSeller && $active) {
            if ($isSellerGroup) {
                if (!$this->mpHelper->isAllowedAction('multiship/shipping/view')) {
                    return false;
                }
                return true;
            }
            return true;
        }
        return false;
    }

    /**
     * Return link url
     *
     * @param string $url
     * @return string
     */
    public function getActionUrl($url)
    {
        return $this->url->getUrl($url, [
            '_secure' => $this->request->isSecure()
        ]);
    }

    /**
     * Get list of active shipping methods supported by multishipping
     *
     * @return array
     */
    public function getActiveCarriers()
    {
        return $this->helper->getActiveCarriers();
    }
}
