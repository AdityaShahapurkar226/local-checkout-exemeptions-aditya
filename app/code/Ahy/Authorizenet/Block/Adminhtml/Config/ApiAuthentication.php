<?php
namespace Ahy\Authorizenet\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Ahy\Authorizenet\Service\AuthorizeNetApi;
use Ahy\Authorizenet\Model\ConfigProvider;
use Magento\Framework\Encryption\EncryptorInterface;


class ApiAuthentication extends Field
{
    protected $_authorizeNetApi;
    protected $encryptor;
    private $configProvider;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        AuthorizeNetApi $authorizeNetApi,
        EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->_authorizeNetApi = $authorizeNetApi;
        $this->configProvider = $configProvider;
        $this->encryptor = $encryptor;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve the API credentials from the admin configuration
     *
     * @return array
     */
    protected function getApiCredentials()
    {   
        $storeId = $this->_storeManager->getStore()->getId();
        $apiLoginId = $this->configProvider->getLogin();
        $transactionKey = $this->configProvider->getTransKey();
        $accountType = $this->configProvider->getAccountType();

        return [
            'apiLoginId' => $apiLoginId,
            'storeId' => $storeId,
            'accountType' => $accountType,
            'transactionKey' => $transactionKey
        ];
    }

    /**
     * Check API Authentication status and display the result
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {   
        $apiCredentials = $this->getApiCredentials();
        $loginKey = $apiCredentials['apiLoginId'];
        $transKey = $this->_decryptValue($apiCredentials['transactionKey']);
        $apiTestResponse = 'Enter API credentials and save to test.';
        if($loginKey && $transKey){
            $apiTestResponseJson = $this->_authorizeNetApi->testAuthorizeAPI($loginKey, $transKey);
            $apiTestResponse = $this->_handleApiTestResponse($apiTestResponseJson);
        }
        return $apiTestResponse;
    }

    private function _decryptValue($value)
    {
        return $this->encryptor->decrypt($value);
    }
    private function _handleApiTestResponse($response)
    {
        $responseData = json_decode($response);

        if (isset($responseData->messages->resultCode) && $responseData->messages->resultCode === 'Ok') {
            return "<span style='color: green;'>API test successful!</span>";
        } elseif (isset($responseData->messages->message[0]->code) && isset($responseData->messages->message[0]->text)) {
            $errorCode = $responseData->messages->message[0]->code;
            $errorText = $responseData->messages->message[0]->text;

            switch ($errorCode) {
                case 'E00005':
                case 'E00006':
                case 'E00007':
                case 'E00008':
                    // Bad login ID / trans key
                    return "<span style='color: red;'>Your API credentials are invalid. ($errorCode)</span>";
                    break;
                case 'E00009':
                    // Test mode active
                    return "<span style='color: red;'>Your account has test mode enabled. It must be disabled for CIM to work properly. ($errorCode)</span>";
                    break;
                case 'E00044':
                    // CIM not enabled
                    return "<span style='color: red;'>Your account does not have CIM enabled. Please contact your Authorize.Net support rep to resolve this. ($errorCode)</span>";
                    break;
                default:
                    // Other error codes
                    return "<span style='color: red;'>$errorText ($errorCode)</span>";
                    break;
            }
        }
        return 'Enter API credentials and save to test.';
    }


}
