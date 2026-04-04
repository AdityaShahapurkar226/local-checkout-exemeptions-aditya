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

class Shipment extends \Webkul\Marketplace\Model\Order\Pdf\Shipment
{
    /**
     * Construct function
     *
     * @param \Webkul\Marketplace\Model\Seller $collection
     * @param \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
     */
    public function __construct(
        \Webkul\Marketplace\Model\Seller $collection,
        \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
    ) {
        $this->sellerSubAccountHelper = $sellerSubAccountHelper;
        $this->collection = $collection;
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
        $_Sellerimg = '';
        $sellerId = $this->_getSession()->getCustomerId();
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $this->collection
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId);
        foreach ($this->collection as $row) {
            $_Sellerimg = $row->getLogoPic();
            if ($_Sellerimg) {
                $sellerImageFlag = 1;
            }
        }

        if ($_Sellerimg == '') {
            $_Sellerimg = $this->_scopeConfig
            ->getValue(
                'sales/identity/logo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            $sellerImageFlag = 0;
        }
        $this->y = $this->y ? $this->y : 815;
        if ($_Sellerimg) {
            if ($sellerImageFlag == 0) {
                $sellerImagePath = '/sales/store/logo/'.$_Sellerimg;
            } else {
                $sellerImagePath = '/avatar/'.$_Sellerimg;
            }
            if ($this->_mediaDirectory->isFile($sellerImagePath)) {
                $_Sellerimg = \Zend_Pdf_Image::imageWithPath(
                    $this->_mediaDirectory->getAbsolutePath($sellerImagePath)
                );
                $imageTop = 830; //top border of the page
                $imageWidthLimit = 270; //image width half of the page width
                $imageHeightLimit = 270;
                $imageWidth = $_Sellerimg->getPixelWidth();
                $imageHeight = $_Sellerimg->getPixelHeight();

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
                $sellerPdfPage->drawImage($_Sellerimg, $x1Axis, $y1Axis, $x2Axis, $y2Axis);
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
        $this->y = $this->y ? $this->y : 815;
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        $imageTop = 815;
        $sellerId = $this->_getSession()->getCustomerId();
        $address = '';
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

        foreach (explode("\n", $address) as $value1) {
            if ($value1 !== '') {
                $value1 = preg_replace('/<br[^>]*>/i', "\n", $value1);
                foreach ($this->string->split($value1, 45, true, true) as $_value2) {
                    $sellerPdfPage->drawText(
                        trim(strip_tags($_value2)),
                        $this->getAlignRight($_value2, 130, 440, $font, 10),
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
