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
namespace Webkul\SellerSubAccount\Block\View\Html;

class SwitchLink extends \Webkul\Marketplace\Block\View\Html\SwitchLink
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\SellerSubAccount\Helper\Data
     */
    private $subAccountHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param \Webkul\SellerSubAccount\Helper\Data             $subAccountHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\SellerSubAccount\Helper\Data $subAccountHelper,
        array $data = []
    ) {
        parent::__construct($context, $helper, $data);
        $this->helper = $helper;
        $this->subAccountHelper = $subAccountHelper;
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        if ($this->helper->getIsSeparatePanel()) {
            $subAccount = $this->subAccountHelper->getCurrentSubAccount();
            if ($subAccount->getId()) {
                $allowedActions = $this->subAccountHelper->getAllAllowedActions();
                $path = $this->escapeHtml($this->getPath());
                if (empty($allowedActions[$path]) && !empty($allowedActions)) {
                    foreach ($allowedActions as $key => $value) {
                        $path = $key;
                        break;
                    }
                    return $this->getUrl($path);
                }
            }
        }
        return $this->getUrl($this->getPath());
    }
}
