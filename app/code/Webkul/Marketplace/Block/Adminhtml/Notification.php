<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block\Adminhtml;

class Notification extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $configProvider;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\Marketplace\Model\Notification\MarketplaceConfigProvider $configProvider
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Marketplace\Model\Notification\MarketplaceConfigProvider $configProvider,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get json helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
    /**
     * Get notification config
     *
     * @return array
     */
    public function getNotificationConfig()
    {
        $configData = $this->configProvider->getConfig();
        return $configData;
    }
}
