<?php 
namespace Ahy\ThemeCustomization\Block;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Review\Block\Product\View as ReviewBlock;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Review\Model\ReviewFactory;

class SponsoredProduct extends \Magento\Framework\View\Element\Template
{
    protected $categoryFactory;
    protected $categoryRepository;
    protected $collectionFactory;
    protected $imageHelper;
    protected $reviewFactory;

    public function __construct(
        ProductContext $context,
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory,
        Image $imageHelper,
        ReviewFactory $reviewFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->imageHelper = $imageHelper;
        $this->collectionFactory = $collectionFactory;
        $this->reviewFactory = $reviewFactory;
    }

    public function getCategoryDetails($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoryFilter($category)
            ->load();
        return $collection;
    }
    public function getImageUrl(Product $product, $imageType)
    {
        return $this->imageHelper->init($product, $imageType)->getUrl();
    }
    public function getReviews(Product $product)
    {
        $reviews = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('entity_pk_value', $product->getId())
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        $ratingSum = 0;
        $reviewCount = 0;
        foreach ($reviews as $review) {
            $ratingSum += $review->getRatingVotes()->getFirstItem()->getPercent();
            $reviewCount++;
        }
        $averageRating = $reviewCount > 0 ? round($ratingSum / $reviewCount) : 0;
        return [
            'rating' => $averageRating,
            'count' => $reviewCount
        ];
    }
}
?>