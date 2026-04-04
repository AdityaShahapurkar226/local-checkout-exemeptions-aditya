<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Block;

use Magento\Framework\Json\Helper\Data as jsonHelper;
use Webkul\Marketplace\Helper\Data as mpHelper;
use Webkul\MpSellerBuyerCommunication\Helper\Data as commHelper;

class Helper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    protected $_commHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param jsonHelper $jsonHelper
     * @param mpHelper $mpHelper
     * @param commHelper $commHelper
     * @param \Magento\Cms\Helper\Wysiwyg\Images|null $wysiwygImages
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        jsonHelper $jsonHelper,
        mpHelper $mpHelper,
        commHelper $commHelper,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null,
        array $data = []
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_mpHelper = $mpHelper;
        $this->_commHelper = $commHelper;
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Json Helper
     *
     * @return void
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * Get Mp Helper
     *
     * @return void
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }

    /**
     * Get Comm Helper
     *
     * @return void
     */
    public function getCommHelper()
    {
        return $this->_commHelper;
    }
    /**
     * Get wysiwyg url
     *
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }
}
