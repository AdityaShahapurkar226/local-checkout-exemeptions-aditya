<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\Config\Source;

class Customer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $groupCollection;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection
    ) {
        $this->groupCollection = $groupCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];
        $groupOptions = $this->groupCollection;
        foreach ($groupOptions->getData() as $key => $group) {
            $data[$key]['value'] = $group['customer_group_id'];
            $data[$key]['label'] = $group['customer_group_code'];
        }
        return $data;
    }
}
