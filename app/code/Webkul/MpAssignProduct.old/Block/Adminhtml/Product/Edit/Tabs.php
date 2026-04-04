<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Block\Adminhtml\Product\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $block = \Webkul\MpAssignProduct\Block\Adminhtml\Product\Edit\Tab\Details::class;
        $url = $this->getUrl('*/*/conversation', ['_current' => true]);
        $this->addTab(
            'product_info',
            [
                'label' => __('Product Information'),
                'content' => $this->getLayout()
                                ->createBlock($block, 'product_info')
                                ->setTemplate("product.phtml")
                                ->toHtml(),
            ]
        );
        
        return parent::_prepareLayout();
    }
}
