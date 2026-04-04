<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Block\Sales\Order\Items;

use Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer as Subject;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;

class RendererPlugin
{
    const ATTRIBUTES_INFO = 'attributes_info';

    /**
     * @param Subject $subject
     * @param string $result
     * @param OrderItem|InvoiceItem $item
     * @return string
     */
    public function afterGetValueHtml($subject, $result, $item)
    {
        $orderItem = $item;
        if ($item instanceof InvoiceItem || $item instanceof CreditMemoItem) {
            $orderItem = $item->getOrderItem();
        }

        if ($orderItem instanceof OrderItem && $orderItem->getProductType() == Configurable::TYPE_CODE
            && $attributesInfo = $orderItem->getProductOptionByCode(static::ATTRIBUTES_INFO)
        ) {
            foreach ($attributesInfo as $attributeInfo) {
                $result .= sprintf('<br />%s: %s', $attributeInfo['label'], $attributeInfo['value']);
            }
        }
        return $result;
    }
}
