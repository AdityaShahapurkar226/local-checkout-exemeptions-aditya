<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Sellerlist implements OptionSourceInterface
{
    /** @var CollectionFactory */
    protected $collectionFactory;

    /** @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory */
    protected $sellerCollectionFactory;
    
    /**
     * Initialize
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
    }
    /**
     * Get customer options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $sellers = $this->getSellerCollection();
        foreach ($sellers as $seller) {
            $options[] = [
                'value' => $seller->getId(),
                'label' => $seller->getFirstname().' '.$seller->getLastname(),
            ];
        }
       
        return $options;
    }
    /**
     * Get SellerCollection
     *
     * @return object
     */
    protected function getSellerCollection()
    {
        $sellerIds = $this->sellerCollectionFactory->create()
        ->addFieldToFilter("is_seller", ['eq' => 1])
        ->addFieldToSelect("seller_id")->getData();
        $collection = $this->collectionFactory->create()->addFieldToFilter("entity_id", ['in' => $sellerIds]);
        
        return $collection;
    }
}
