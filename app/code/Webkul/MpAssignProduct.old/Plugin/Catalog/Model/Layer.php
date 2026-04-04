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

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
    }

    /**
     * Plugin for getProductCollection
     *
     * @param \Magento\Catalog\Model\Layer $subject
     * @return $result
     */
    public function afterGetProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        $result
    ) {
        $assignProductsIds = $this->helper->getCollection()->getAllIds();
        $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
        $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        if (!empty($assignProductsIds)) {
            $result->addAttributeToFilter('entity_id', ['nin' => $assignProductsIds]);
        }
        return $result;
    }
}
