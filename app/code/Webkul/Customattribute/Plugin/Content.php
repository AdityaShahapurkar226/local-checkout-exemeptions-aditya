<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Plugin;

class Content
{
    /**
     * Constructor function
     *
     * @param \Webkul\Customattribute\Block\Manageattribute $attribute
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\Customattribute\Block\Manageattribute $attribute,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->attribute =  $attribute;
        $this->request  = $request;
    }
    /**
     * After get allowed media attributes
     *
     * @param \Webkul\Marketplace\Block\Product\Helper\Form\Gallery\Content $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllowedMediaAttributes(
        \Webkul\Marketplace\Block\Product\Helper\Form\Gallery\Content $subject,
        $result
    ) {
        $product_id = $this->request->getParam('id');
        $product = $this->attribute->getProductCollection($product_id);
        if ($this->request->getParam('set')) {
            $attributeSetId = $this->request->getParam('set');
        } else {
            $attributeSetId = $product['attribute_set_id'];
        }
        $readresult = $this->attribute->getFrontShowAttributes($attributeSetId);
        foreach ($readresult as $attr) {
            $attribute = $this->attribute->getCatalogResourceEavAttribute($attr['attribute_id']);
            $attributeCode = $attribute['attribute_code'];
            if ($attribute['frontend_input'] == 'media_image') {
                array_push($result, $attributeCode);
            }
        }
        return $result;
    }
}
