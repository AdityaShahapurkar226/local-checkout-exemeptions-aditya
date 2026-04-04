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

namespace Webkul\MpSellerBuyerCommunication\Ui\Component;

/**
 * Class ExportButton for the grid on the admin side.
 */
class ExportButton extends \Magento\Ui\Component\AbstractComponent
{
    /**
     * Component name
     */
    public const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Request\Http $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->_request = $request;
    }

    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $commentId = $this->_request->getParam('comm_id');
        if (isset($commentId)) {
            $configData = $this->getData('config');
            if (isset($configData['options'])) {
                $configOptions = [];
                foreach ($configData['options'] as $configOption) {
                    $configOption['url'] = $this->_urlBuilder->getUrl(
                        $configOption['url'],
                        ["comm_id"=>$commentId]
                    );
                    $configOptions[] = $configOption;
                }
                $configData['options'] = $configOptions;
                $this->setData('config', $configData);
            }
        }
        parent::prepare();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
