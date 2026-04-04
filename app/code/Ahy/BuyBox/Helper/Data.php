<?php
// File: app/code/Ahy/BuyBox/Helper/Data.php

namespace Ahy\BuyBox\Helper;
use Ahy\BuyBox\Service\UserGeoLocation;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    
    /**
     * Undocumented function
     *
     * @var \Webkul\MpAssignProduct\Helper\Data $helper
     * @var \Webkul\Marketplace\Helper\data $marketplaceHelper
     * @var UserGeoLocation $userGeoLocation
     */
    protected $helper;
    protected $_marketplaceHelper;
    protected $_userGeoLocation;
    
    /**
     * Undocumented function
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\data $marketplaceHelper
     * @param UserGeoLocation $userGeoLocation
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\Marketplace\Helper\data $marketplaceHelper,
        UserGeoLocation $userGeoLocation
    ) {
        $this->helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper; 
        $this->_userGeoLocation = $userGeoLocation;
    }
    /**
     * Get Minimum price product
     *
     * @param int $productId
     * @return int|bool
     */
    public function getMinimumPriceProducts($productId)
    {   
        if ($this->helper->showMinimumPrice()) {
            $assignProductIds = $this->helper->getCollection()->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('condition', 1)->getAllIds();
            $assignProductCollection = $this->helper->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('condition', 1);

            $assignProductData = $assignProductCollection->getData();
            if (!count($assignProductIds)) {
                return false;
            }
            $assignProductIds[] = $productId;
            $productCollection = $this->helper->getProductCollection()
                        ->addAttributeToSelect('price')
                        ->addFieldToFilter('entity_id', ['in' => $assignProductIds]);
            $productCollection->addAttributeToSort('price', 'ASC');
            return $productCollection->getFirstItem()->getId();
        } else {
            return false;
        }
        return false;
    }

    public function getMinimumPriceWithProximityAssignment($productId)
    {
        if ($this->helper->showMinimumPrice()) {
            $assignProductIds = $this->helper->getCollection()->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('condition', 1)->getAllIds();

            $assignProductCollection = $this->helper->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('condition', 1);

            $assignProductData = $assignProductCollection->getData();

            if (!count($assignProductIds)) {
                return false;
            }

            $assignProductIds[] = $productId;

            $productCollection = $this->helper->getProductCollection()
                ->addAttributeToSelect('price')
                ->addFieldToFilter('entity_id', ['in' => $assignProductIds]);

            $productCollection->addAttributeToSort('price', 'ASC');
            $lowestPricedProductId = $productCollection->getFirstItem()->getId();

            // Generate random proximity scores
            $proximityWeight = 0.4;
            $priceWeight = 0.6;

            $proximityScores = [];
            $userPostalCode = $_COOKIE["postalCode"];
            $userLatLongData = $this->_userGeoLocation->getAddressCoordinates($userPostalCode);
            foreach ($assignProductData as $data) {
                $sellerId = $data['seller_id'];
                $sellerDataObj = $this->_marketplaceHelper->getSellerDataBySellerId($sellerId);
                $sellerData = $sellerDataObj->getData()[0];
                $distanceFromUserAndSeller = $this->_userGeoLocation->calculateFflDistanceFromUserInMiles($userLatLongData, $sellerData);
                $proximity = number_format($distanceFromUserAndSeller[0], 2);
                $proximityScores[$data['assign_product_id']] = $proximity;
            }
            // print_r($proximityScores);

            // Calculate normalized price scores
            $minPrice = $productCollection->getFirstItem()->getPrice();
            $maxPrice = $productCollection->getLastItem()->getPrice();
            $priceScores = [];

            foreach ($assignProductData as $data) {
                $normalizedPrice = ($data['price'] - $minPrice) / ($maxPrice - $minPrice);
                $priceScores[$data['assign_product_id']] = $normalizedPrice;
            }

            // Calculate weighted scores
            $weightedScores = [];
            foreach ($assignProductData as $data) {
                $assignProductId = $data['assign_product_id'];
                
                // Adjust the calculation based on the weightage
                $weightedScore = ($priceWeight * $priceScores[$assignProductId]) - ($proximityWeight * $proximityScores[$assignProductId]);
                $weightedScores[$assignProductId] = $weightedScore;
            }

            // Find the assign_product_id with the lowest weighted score
            $winningAssignProductId = array_search(max($weightedScores), $weightedScores);

            // var_dump($winningAssignProductId);
            return $winningAssignProductId;
        } else {
            return false;
        }
    }


    public function getAssignProductCollection($productId){
        $assignProductIds = $this->helper->getAssignProductCollection($productId);
        return $assignProductIds;
    }

    public function ahyBuyBox($productId){  

        return $this->getMinimumPriceWithProximityAssignment($productId);
    }
}
