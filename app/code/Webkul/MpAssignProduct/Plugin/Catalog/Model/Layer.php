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

namespace Webkul\MpAssignProduct\Plugin\Catalog\Model;

class Layer
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpAssignProduct\Model\AssociatesFactory
     */
    protected $associatesFactory;

    /** @var RequestInterface */
    protected $request;

    /**
     * Initialization
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->request = $request;
    }

    /**
     * Plugin for getProductCollection
     *
     * @param \Magento\Catalog\Model\Layer $subject
     * @param [type] $result
     * @return $result
     */
    public function afterGetProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        $result
    ) {
        $assignProductsIds = $this->helper->getAssignProductCategoryCollection()->getAllIds();
        $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
        $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        $actionName = $this->request->getFullActionName();
        if (!empty($assignProductsIds) && $actionName != 'marketplace_seller_collection') {
            $result->addAttributeToFilter('entity_id', ['nin' => $assignProductsIds]);
        }
        return $result;
    }
}
