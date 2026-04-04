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

class Creditmemo extends \Webkul\Marketplace\Model\Order\Pdf\Creditmemo
{
    /**
     * Construct function
     *
     * @param \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper
     * @param \Webkul\Marketplace\Model\Seller $collection
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $sellerSubAccountHelper,
        \Webkul\Marketplace\Model\Seller $collection
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
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
        $sellerId = $this->_getSession()->getCustomerId();
        $_sellerImage = '';
        $sellerImageFlag = 0;
        if ($subAccount->getId()) {
            $sellerId = $this->sellerSubAccountHelper->getSubAccountSellerId();
        }
        $this->collection
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId);
        foreach ($this->collection as $row) {
            $_sellerImage = $row->getLogoPic();
            if ($_sellerImage) {
                $sellerImageFlag = 1;
            }
        }

        if ($_sellerImage == '') {
            $_sellerImage = $this->_scopeConfig
            ->getValue(
                'sales/identity/logo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            $sellerImageFlag = 0;
        }
        $this->y = $this->y ? $this->y : 815;
        if ($_sellerImage) {
            if ($sellerImageFlag == 0) {
                $sellerImagePath = '/sales/store/logo/'.$_sellerImage;
            } else {
                $sellerImagePath = '/avatar/'.$_sellerImage;
            }
            if ($this->_mediaDirectory->isFile($sellerImagePath)) {
                $_sellerImage = \Zend_Pdf_Image::imageWithPath(
                    $this->_mediaDirectory->getAbsolutePath($sellerImagePath)
                );
                $_imageTop = 830; //top border of the page
                $_imageWidthLimit = 270; //image width half of the page width
                $imageHeightLimit = 270;
                $imageWidth = $_sellerImage->getPixelWidth();
                $imageHeight = $_sellerImage->getPixelHeight();

                //preserving seller image aspect ratio
                $imageRatio = $imageWidth / $imageHeight;
                if ($imageRatio > 1 && $imageWidth > $_imageWidthLimit) {
                    $imageWidth = $_imageWidthLimit;
                    $imageHeight = $imageWidth / $imageRatio;
                } elseif ($imageRatio < 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $imageHeight * $imageRatio;
                } elseif ($imageRatio == 1 && $imageHeight > $imageHeightLimit) {
                    $imageHeight = $imageHeightLimit;
                    $imageWidth = $_imageWidthLimit;
                }
                $_y1Axis = $_imageTop - $imageHeight;
                $_y2Axis = $_imageTop;
                $_x1Axis = 25;
                $_x2Axis = $_x1Axis + $imageWidth;
                //seller image coordinates after transformation seller image are rounded by Zend
                $sellerPdfPage->drawImage($_sellerImage, $_x1Axis, $_y1Axis, $_x2Axis, $_y2Axis);
                $this->y = $_y1Axis - 10;
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
        $imageTop = 815;

        $address = '';
        $sellerId = $this->_getSession()->getCustomerId();
        $subAccount = $this->sellerSubAccountHelper->getCurrentSubAccount();
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

        foreach (explode("\n", $address) as $v) {
            if ($v !== '') {
                $v = preg_replace('/<br[^>]*>/i', "\n", $v);
                foreach ($this->string->split($v, 45, true, true) as $_value1) {
                    $sellerPdfPage->drawText(
                        trim(strip_tags($_value1)),
                        $this->getAlignRight($_value1, 130, 440, $font, 10),
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
