<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Plugin;

class NotProtectedExtension
{
    /**
     * Protected extension message key
     */
    public const PROTECTED_EXTENSION = 'protectedExtension';

    /**
     * Protected files config path
     */
    public const XML_PATH_PROTECTED_FILE_EXTENSIONS = 'general/file/protected_extensions';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

   /**
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

   /**
    * Return protected file extensions
    *
    * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $subject
    * @param callable $proceed
    * @param int $store
    * @return array
    */
    public function aroundGetProtectedFileExtensions(
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $subject,
        callable $proceed,
        $store = null
    ) {
        $updatedExtensionArray = [];
        $extensionArray =  $this->_scopeConfig->getValue(
            self::XML_PATH_PROTECTED_FILE_EXTENSIONS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        foreach ($extensionArray as $type => $data) {
            if ($type == 'xml') {
                continue;
            }
            array_push($updatedExtensionArray, $data);
        }
        return $updatedExtensionArray;
    }
}
