<?php
namespace Ahy\GooglePay\Block\Paypal\Express\Review;

use Magento\Paypal\Block\Express\Review\Details as CorePaypalDetails;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\ConfigInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class Details extends CorePaypalDetails
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ConfigInterface $salesConfig,
        ImageHelper $imageHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $salesConfig,
            $layoutProcessors,
            $data
        );
    }

    public function getProductImageUrl(Product $product, $imageType = 'cart_page_product_thumbnail')
    {
        return $this->imageHelper->init($product, $imageType)->getUrl();
    }

    public function formatPrice($price)
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->format($price, [], false);
    }
}
