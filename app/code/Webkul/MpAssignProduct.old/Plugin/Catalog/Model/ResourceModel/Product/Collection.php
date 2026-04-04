<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Plugin\Catalog\Model\ResourceModel\Product;

class Collection
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Webkul\MpAssignProduct\Model\AssociatesFactory
     */
    protected $associatesFactory;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     * @param \Magento\Framework\App\State     $appState
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->_appState = $appState;
    }

    public function aroundAddAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        \Closure $proceed,
        $attribute,
        $joinType = false
    ) {
        $appState = $this->_appState;
        $areCode = $appState->getAreaCode();
        $result = $proceed($attribute, $joinType = false);
        $code = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
        if ($appState->getAreaCode() == $code) {
            $assignProductsIds = $this->helper->getCollection()->getAllIds();
            $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
            $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
            if (!empty($assignProductsIds)) {
                $result->addFieldToFilter('entity_id', ['nin' => $assignProductsIds]);
            }
        }
        return $result;
    }
}
