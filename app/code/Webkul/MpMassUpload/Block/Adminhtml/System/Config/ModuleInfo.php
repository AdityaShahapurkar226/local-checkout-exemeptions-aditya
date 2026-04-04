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
namespace Webkul\MpMassUpload\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Framework\Module\PackageInfoFactory;

class ModuleInfo extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var PackageInfoFactory
     */
    protected $_packageInfoFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $label = $element->getLabel();
        $packageInfo = $this->_packageInfoFactory->create();
        $version = $packageInfo->getVersion('Webkul_MpMassUpload');
        $label .= __('<p>Version: %1</p>', $version);
        
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $label
        );
    }
}
