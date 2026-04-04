<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
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

    /** @var \Magento\Framework\App\Request\Http */
    protected $_request;

    /** @var \Webkul\MpAssignProduct\Logger\Logger */
    protected $logger;

    /**
     * Initialization
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\MpAssignProduct\Logger\Logger $logger
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MpAssignProduct\Logger\Logger $logger
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->_appState = $appState;
        $this->_request = $request;
        $this->logger = $logger;
    }

    /**
     * Hide products from product grid
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param array $result
     * @param [type] $attribute
     * @param boolean $joinType
     * @return array
     */
    public function afterAddAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $result,
        $attribute,
        $joinType = false
    ) {
        $appState = $this->_appState;
        $areCode = $appState->getAreaCode();
       // $result = $proceed($attribute, $joinType = false);
        $actionName = $this->_request->getFullActionName();
        $code = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
       // if ($appState->getAreaCode() == $code) {
            $assignProductsIds = $this->helper->getCollection()->getAllIds();
            $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
            $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        if (!empty($assignProductsIds)) {
            $this->logger->info(json_encode($assignProductsIds));
            $this->logger->info($actionName);
            if ($actionName == 'mui_index_render' ||
                $actionName == 'catalog_category_edit' ||
                $actionName == 'catalog_category_grid' ||
                $actionName == 'marketplace_mui_index_render'
            ) {
                $result->addFieldToFilter('entity_id', ['nin' => $assignProductsIds]);
            }
        }
       // }
        return $result;
    }
}
