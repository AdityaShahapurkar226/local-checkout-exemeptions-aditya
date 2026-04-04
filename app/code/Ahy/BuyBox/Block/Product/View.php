<?php

namespace Ahy\BuyBox\Block\Product;

use Magento\Catalog\Block\Product\View as MagentoProductView;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ahy\BuyBox\Service\UserGeoLocation;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory as AssignProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Ahy\BuyBox\Helper\Data;

class View extends MagentoProductView
{
    /**
     * @var UserGeoLocation
     */
    private $userGeoLocation;
    /**
     * @var AssignProductCollection
     */
    protected $_assignProductCollection;
    /**
     * @var CollectionFactory
     */
    protected $_mpProductCollection;

    protected $_helper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        CollectionFactory $mpProductCollectionFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        AssignProductCollection $assignProductCollection,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        Data $helperData,
        UserGeoLocation $userGeoLocation,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->userGeoLocation = $userGeoLocation;
        $this->_assignProductCollection = $assignProductCollection;
        $this->_mpProductCollection = $mpProductCollectionFactory;
        $this->_helper = $helperData;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    public function getSellerInfo()
    {
        try {
            
            $sellerAssignCollection = $this->_assignProductCollection
                ->create()
                ->addFieldToFilter('product_id', 7)
                ->addFieldToSelect('product_id');
            foreach ($sellerAssignCollection as $data){
                print_r($data);
            }
            // var_dump($sellerAssignCollection);
            // exit;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }


    /**
     * Get the UserGeoLocation instance.
     *
     * @return UserGeoLocation
     */
    public function calculateTheDistanceFromUserToVendor($userPinCode, $sellerLatLongData)
    {
        $userLatLong = $this->userGeoLocation->getAddressCoordinates($userPinCode);
        $distanceFromUserToSellers = $this->userGeoLocation->calculateFflDistanceFromUserInMiles($userLatLong, $sellerLatLongData);
        return $distanceFromUserToSellers;
    }

    public function calculateSellerRanking($sellers, $userLocation)
    {
        $priceWeight = 0.5;
        $proximityWeight = 0.25;
        $ratingWeight = 0.25;
        $referencePriceRange = 100; // Adjust according to your price range
        $referenceProximityRange = 100; // Adjust according to your proximity range

        // Calculate normalized values and ranking scores for each seller
        foreach ($sellers as &$seller) {
            // Normalize price
            $normalizedPrice = $seller['price'] / $referencePriceRange;

            // Calculate proximity from customer
            $proximity = calculateProximity($seller['location'], $userLocation);
            // Normalize proximity
            $normalizedProximity = $proximity / $referenceProximityRange;

            // Normalize rating based on number of ratings
            $normalizedRating = $seller['rating'] * ($seller['num_ratings'] / $maxNumRatings);

            // Calculate ranking score
            $rankingScore = ($priceWeight       * $normalizedPrice)     + 
                            ($proximityWeight   * $normalizedProximity) +
                            ($ratingWeight      * $normalizedRating);

            $seller['ranking_score'] = $rankingScore;
        }

        // Sort sellers based on ranking score in descending order
        usort($sellers, function ($a, $b) {
            return $b['ranking_score'] <=> $a['ranking_score'];
        });

        return $sellers;
    }

    // Helper function to calculate proximity between two locations (latitude, longitude)
    function calculateProximity($location1, $location2)
    {
        // Implement your logic to calculate proximity using latitude and longitude
        // You can use distance calculation algorithms like Haversine or Vincenty's formulae
        // Return the proximity value (e.g., distance in miles or kilometers)
    }

}
