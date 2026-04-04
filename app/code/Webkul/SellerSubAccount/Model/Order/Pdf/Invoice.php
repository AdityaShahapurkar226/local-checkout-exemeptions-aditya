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

namespace Webkul\SellerSubAccount\Model\Order\Pdf;

class Invoice extends \Webkul\Marketplace\Model\Order\Pdf\Invoice
{
    /**
     * Construct function
     *
     * @param \Webkul\SellerSubAccount\Helper\Data $_sellerSubAccountHelper
     * @param \Webkul\Marketplace\Model\Seller $_collection
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $_sellerSubAccountHelper,
        \Webkul\Marketplace\Model\Seller $_collection
    ) {
        $this->sellerSubAccountHelper = $_sellerSubAccountHelper;
        $this->collection = $_collection;
    }
    /**
     * Insert Seller logo to seller pdf page.
     *
     * @param \Zend_Pdf_Page $sellerPdfPage
     * @param null           $store
     */
    public function insertLogo(&$sellerPdfPage, $store = null)
    {
        $sellerImageFlag = 0;
        $sellerImage = '';
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        $sellerId = $this->_getSession()->getCustomerId();
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $this->collection
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId);
        foreach ($this->collection as $row) {
            $sellerImage = $row->getLogoPic();
            if ($sellerImage) {
                $sellerImageFlag = 1;
            }
        }

        if ($sellerImage == '') {
            $sellerImage = $this->_scopeConfig
            ->getValue(
                'sales/identity/logo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            $sellerImageFlag = 0;
        }
        $this->y = $this->y ? $this->y : 815;
        if ($sellerImage) {
            if ($sellerImageFlag == 0) {
                $sellerImagePath = '/sales/store/logo/'.$sellerImage;
            } else {
                $sellerImagePath = '/avatar/'.$sellerImage;
            }
            if ($this->_mediaDirectory->isFile($sellerImagePath)) {
                $sellerImage = \Zend_Pdf_Image::imageWithPath(
                    $this->_mediaDirectory->getAbsolutePath($sellerImagePath)
                );
                $imageTop = 830; //top border of the page
                $imageWidthLimit = 270; //image width half of the page width
                $imageHeightLimit = 270;
                $imageWidth = $sellerImage->getPixelWidth();
                $imageHeight = $sellerImage->getPixelHeight();

                //preserving seller image aspect ratio
                $imageRatio = $imageWidth / $imageHeight;
                
                if ($imageRatio > 1 && $imageWidth > $imageWidthLimit) {
                    $imageWidth = $imageWidthLimit;
                    $imageHeight = $imageWidth / $imageRatio;
                } elseif ($imageRatio < 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $imageHeight * $imageRatio;
                } elseif ($imageRatio == 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $imageWidthLimit;
                }
                $y1Axis = $imageTop - $imageHeight;
                $y2Axis = $imageTop;
                $x1Axis = 25;
                $x2Axis = $x1Axis + $imageWidth;
                //seller image coordinates after transformation seller image are rounded by Zend
                $sellerPdfPage->drawImage($sellerImage, $x1Axis, $y1Axis, $x2Axis, $y2Axis);
                $this->y = $y1Axis - 10;
            }
        }
    }

    /**
     * Insert seller address address and other info to pdf page.
     *
     * @param \Zend_Pdf_Page $sellerPdfPage
     * @param null           $store
     */
    public function insertAddress(&$sellerPdfPage, $store = null)
    {
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($sellerPdfPage, 10);
        $sellerPdfPage->setLineWidth(0);
        $imageTop = 815;
        $address = '';
        $this->y = $this->y ? $this->y : 815;
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        $sellerId = $this->_getSession()->getCustomerId();
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $this->collection
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId);
        foreach ($this->collection as $row) {
            $address = $row->getOthersInfo();
        }

        if ($address == '') {
            $address = $this->_scopeConfig->getValue(
                'sales/identity/address',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        foreach (explode("\n", $address) as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $sellerPdfPage->drawText(
                        trim(strip_tags($_value)),
                        $this->getAlignRight($_value, 130, 440, $font, 10),
                        $imageTop,
                        'UTF-8'
                    );
                    $imageTop -= 10;
                }
            }
        }
        $this->y = $this->y > $imageTop ? $imageTop : $this->y;
    }
}
