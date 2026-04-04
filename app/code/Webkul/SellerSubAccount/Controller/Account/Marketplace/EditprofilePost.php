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

namespace Webkul\SellerSubAccount\Controller\Account\Marketplace;

class EditprofilePost extends \Webkul\Marketplace\Controller\Account\EditprofilePost
{
    /**
     * Update Seller Profile Informations.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
                $errors = $this->validateprofiledata($fields);
                $sellerId = $this->getCustomerId();
                $sellerSubAccountHelper = $this->_objectManager->create(
                    \Webkul\SellerSubAccount\Helper\Data::class
                );
                $subAccount = $sellerSubAccountHelper->getCurrentSubAccount();
                if ($subAccount->getId()) {
                    $sellerId = $sellerSubAccountHelper->getSubAccountSellerId();
                }
                $img1 = '';
                $img2 = '';
                if (empty($errors)) {
                    updateSellerData();
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Update Seller Data
     *
     * @return void
     */
    protected function updateSellerData()
    {
        $autoId = 0;
        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Seller::class
        )
        ->getCollection()
        ->addFieldToFilter('seller_id', $sellerId);
        foreach ($collection as $value) {
            $autoId = $value->getId();
        }
        $fields = $this->getSellerProfileFields($fields);

        $value = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Seller::class
        )->load($autoId);
        $value->addData($fields);
        $value->setUpdatedAt($this->_date->gmtDate());
        $value->save();

        if ($fields['company_description']) {
            $fields['company_description'] = str_replace(
                'script',
                '',
                $fields['company_description']
            );
        }
        $value->setCompanyDescription($fields['company_description']);

        if (isset($fields['return_policy'])) {
            $fields['return_policy'] = str_replace(
                'script',
                '',
                $fields['return_policy']
            );
            $value->setReturnPolicy($fields['return_policy']);
        }

        if (isset($fields['shipping_policy'])) {
            $fields['shipping_policy'] = str_replace(
                'script',
                '',
                $fields['shipping_policy']
            );
            $value->setShippingPolicy($fields['shipping_policy']);
        }

        $value->setMetaDescription($fields['meta_description']);

        /**
         * set taxvat number for seller
         */
        if ($fields['taxvat']) {
            $customer = $this->_objectManager->create(
                \Magento\Customer\Model\Customer::class
            )->load($sellerId);
            $customer->setTaxvat($fields['taxvat']);
            $customer->setId($sellerId)->save();
        }

        $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(
                ['fileId' => 'banner_pic']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            if ($result['file']) {
                $value->setBannerPic($result['file']);
            }
        } catch (\Exception $e) {
            if ($e->getMessage()!='The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }
        try {
            /** @var $uploaderLogo \Magento\MediaStorage\Model\File\Uploader */
            $uploaderLogo = $this->_fileUploaderFactory->create(
                ['fileId' => 'logo_pic']
            );
            $uploaderLogo->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploaderLogo->setAllowRenameFiles(true);
            $resultLogo = $uploaderLogo->save($target);
            if ($resultLogo['file']) {
                $value->setLogoPic($resultLogo['file']);
            }
        } catch (\Exception $e) {
            if ($e->getMessage()!='The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }

        if (array_key_exists('country_pic', $fields)) {
            $value->setCountryPic($fields['country_pic']);
        }
        $value->save();

        if (array_key_exists('country_pic', $fields)) {
            $value->setCountryPic($fields['country_pic']);
        }
        $value->save();
        try {
            if (!empty($errors)) {
                foreach ($errors as $message) {
                    $this->messageManager->addError($message);
                }
            } else {
                $this->messageManager->addSuccess(
                    __('Profile information was successfully saved')
                );
            }

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t save the customer.'));
        }

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/editProfile',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    /**
     * Get Customer Id
     *
     * @return void
     */
    public function getCustomerId()
    {
        return $this->_getSession()->getCustomerId();
    }

    /**
     * Validate Profile Data
     *
     * @param array $fields
     * @return void
     */
    protected function validateprofiledata(&$fields = [])
    {
        $errors = [];
        $data = [];
        foreach ($fields as $code => $value) {
            switch ($code):
                case 'twitter_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Twitterid cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Facebookid cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'instagram_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Instagram ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'gplus_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Google Plus ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'youtube_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Youtube ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'vimeo_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Vimeo ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'pinterest_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Pinterest ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'moleskine_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Moleskine ID cannot contain space and special characters,
                        allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'taxvat':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)
                    ) {
                        $errors[] = __('Tax/VAT Number cannot contain space and special characters');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'shop_title':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'contact_number':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_locality':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'meta_keyword':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'meta_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'shipping_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;

                case 'return_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'background_width':
                    if (trim($value) != '' &&
                        strlen($value) != 6 &&
                        substr($value, 0, 1) != '#'
                    ) {
                        $errors[] = __('Invalid Background Color');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
            endswitch;
        }

        return $errors;
    }
}
