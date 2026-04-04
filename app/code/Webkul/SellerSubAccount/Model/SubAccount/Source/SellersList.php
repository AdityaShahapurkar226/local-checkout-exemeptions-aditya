<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Model\SubAccount\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class SellersList implements OptionSourceInterface
{
    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\Grid
     */
    public $seller;

    /**
     * Constructor
     *
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\Grid $seller
     */
    public function __construct(
        \Webkul\Marketplace\Model\ResourceModel\Seller\Grid\Collection $seller
    ) {
        $this->seller = $seller;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $sellerCollection = $this->seller;
        $options = [];
        foreach ($sellerCollection as $key => $value) {
            $options[] = [
                'label' => $value->getEmail(),
                'value' => $value->getSellerId(),
            ];
        }
        return $options;
    }
}
