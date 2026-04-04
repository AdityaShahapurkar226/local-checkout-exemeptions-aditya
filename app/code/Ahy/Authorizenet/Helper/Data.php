<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Ahy\Authorizenet\Model\AhyAuthorizeNet;
use Ahy\Authorizenet\Model\ConfigProvider;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    private $_encCollection;
    private $configProvider;
    protected $encryptor;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ConfigProvider $configProvider,
        EncryptorInterface $encryptor,
        AhyAuthorizeNet $encCollection
    ) {
        parent::__construct($context);
        $this->_encCollection = $encCollection;
        $this->configProvider = $configProvider;
        $this->encryptor = $encryptor;
    }

    /**
     * @return bool
     */
    public function generateAuthorizeNetEncryptionKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_-()';
        $length = rand(45, 64);
        $randomString = '';

        // Add at least one alphanumeric character
        $randomString .= $characters[rand(10, 35)];

        // Add at least one capital letter
        $randomString .= chr(rand(65, 90));

        // Add at least one small letter
        $randomString .= chr(rand(97, 122));

        // Add at least one special character
        $randomString .= '$_-()';

        // Generate the remaining random characters
        for ($i = 0; $i < $length - 4; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Shuffle the string to ensure randomness
        $randomString = str_shuffle($randomString);
        
        $encModel = $this->_encCollection->load(1); // load by id, assuming id 1 always exists
        $encModel->setEncryptionKey($randomString);
        $encModel->save();
        
        return $randomString;
    }
    public function getEncryptionKey(){
        $encModel = $this->_encCollection->load(1); // load by id, assuming id 1 always exists
        return $encModel->getEncryptionKey() ?? '';
    }
    /**
     * Retrieve the API credentials from the admin configuration
     *
     * @return array
     */
    public function getApiCredentials()
    {
        $apiLoginId = $this->configProvider->getLogin();
        $transactionKey = $this->configProvider->getTransKey();
        $accountType = $this->configProvider->getAccountType();

        return [
            'apiLoginId' => $apiLoginId,
            'transactionKey' => $transactionKey,
            'accountType' => $accountType
        ];
    }

    public function decryptValue($value){
        return $this->encryptor->decrypt($value);
    }
}
