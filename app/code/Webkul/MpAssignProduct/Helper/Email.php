<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Send email helper class.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected const XML_PATH_EMAIL_SEND_PRODUCT_DELETE_EMAIL = 'mpassignproduct/email/productdelete_toseller_template';

    protected const XML_PATH_EMAIL_SEND_PRODUCT_ASSIGN_EMAIL = 'mpassignproduct/email/assignproduct_toseller_template';
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /** @var \Magento\Framework\Translate\Inline\StateInterface */
    protected $inlineTranslation;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder */
    protected $_transportBuilder;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $_messageManager;

    /** @var $scopeConfig */
    protected $scopeConfig;

    /** @var $tempId */
    protected $tempId;
    
    /**
     * Initializaton
     *
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_messageManager = $messageManager;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }
    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * [generateTemplate description]
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->tempId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * Sendproduct delete email
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendProductDeleteEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_SEND_PRODUCT_DELETE_EMAIL);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Sendproduct delete email
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendAssignProductEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_SEND_PRODUCT_ASSIGN_EMAIL);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Return template id.
     *
     * @param mixed $xmlPath
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }
     /**
      * Return store configuration value.
      *
      * @param string $path
      * @param int    $storeId
      *
      * @return mixed
      */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
