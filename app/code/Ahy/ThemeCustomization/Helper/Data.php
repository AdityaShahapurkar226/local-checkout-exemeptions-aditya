<?php
namespace Ahy\ThemeCustomization\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\MediaStorage\Service\ImageResize;
class Data extends AbstractHelper{
    
    protected   $customerSession;
    protected   $customerFactory;
    private     $_productRepository;
    private     $imageResizeService;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ProductRepositoryInterface $productRepository,
        ImageResize $imageResizeService,
        CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_productRepository = $productRepository;
        $this->imageResizeService = $imageResizeService;
        $this->customerFactory = $customerFactory;
    }
    
    public function getYear()
    {
        return date('Y');
    }

    public function getProductAttribute($productId, $attributeCode)
    {
        $product = $this->_productRepository->getById($productId);
        return $product->getData($attributeCode);
    }

    public function getCustomerShippingRates($sellerId)
    {
        $customer = $this->customerFactory->create()->load($sellerId);
        $customerData = $customer->getData();

        $mpshipping_fixrate = $customerData['mpshipping_fixrate'] ?? null;
        $mpshipping_fixrate_upto = $customerData['mpshipping_fixrate_upto'] ?? null;

        return [
            'mpshipping_fixrate' => $mpshipping_fixrate,
            'mpshipping_fixrate_upto' => $mpshipping_fixrate_upto,
        ];
    }
    public function getImageResizeService($image)
    {
        $this->imageResizeService->resizeFromImageName(originalImageName: $image->getFile());
    }
}

?>