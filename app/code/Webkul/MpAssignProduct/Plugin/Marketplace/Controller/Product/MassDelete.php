<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Plugin\Marketplace\Controller\Product;

/**
 * Webkul Marketplace Product MassDelete controller.
 */
class MassDelete
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

   /**
    * Construct
    *
    * @param \Webkul\MpAssignProduct\Helper\Data $assignHelper
    * @param \Webkul\Marketplace\Helper\Data $helper
    */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $assignHelper,
        \Webkul\Marketplace\Helper\Data $helper
    ) {
        $this->_assignHelper = $assignHelper;
        $this->helper = $helper;
    }

    /**
     * Unassign child product from mass delete
     *
     * @param \Webkul\Marketplace\Controller\Product\Ui\MassDelete $subject
     * @return void
     */
    public function beforeExecute(\Webkul\Marketplace\Controller\Product\Ui\MassDelete $subject)
    {
         
        $wholedata = $subject->getRequest()->getParams();
        
        $ids = isset($wholedata['selected']) ? $wholedata['selected']:[];
        foreach ($ids as $productId) {

            $assignToSeller = $this->_assignHelper->assignToSeller();
            
            if ($assignToSeller || ($this->_assignHelper->hasAssignedProducts($productId)
            && $this->_assignHelper->checkIsAssignProduct($productId))) {
                $sortingInfo = $this->_assignHelper->getSortingOrderInfo();
                $sortBy = $sortingInfo['sort_by'];
                $orderType = $sortingInfo['order_type'];
                $assignProducts = $this->_assignHelper->getTotalProducts($productId, 1, $sortBy, $orderType);
                
                if (count($assignProducts) > 0) {
                    foreach ($assignProducts as $key => $assignproduct) {
                        $proId = $assignproduct['id'];
                        break;
                    }
                    
                    $associatedPro = $this->_assignHelper->getAssignedAssociatedProduct($productId, $proId);
                    foreach ($associatedPro as $associatedProduct) {
                        if (($key = array_search($associatedProduct->getProductId(), $ids)) !== false) {
                            unset($ids[$key]);
                        }
                    }
                }
                
            }
            
        }
        $subject->getRequest()->setPostValue('selected', $ids);
    }
}
