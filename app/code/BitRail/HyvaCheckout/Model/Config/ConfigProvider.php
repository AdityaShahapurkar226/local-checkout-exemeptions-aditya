<?php

namespace Bitrail\HyvaCheckout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigProvider
{
  const CONFIG_BITRAIL_BASE = 'payment/bitrail/';
  const CONFIG_PATH_ENVIRONMENT = self::CONFIG_BITRAIL_BASE . 'environment';
  const CONFIG_PATH_CLIENT_ID = self::CONFIG_BITRAIL_BASE . 'client_id';
  const CONFIG_PATH_CLIENT_SECRET = self::CONFIG_BITRAIL_BASE . 'client_secret';

  const CONFIG_BITRAIL_GATEWAY_BASE = 'payment/bitrail_gateway/';
  const CONFIG_PATH_TITLE = self::CONFIG_BITRAIL_GATEWAY_BASE . 'title';
  const CONFIG_PATH_ORDER_DESCRIPTION = self::CONFIG_BITRAIL_GATEWAY_BASE . 'order_description';
  const CONFIG_VAULT_PREFIX = self::CONFIG_BITRAIL_GATEWAY_BASE . 'vault_handle_';


  private $scopeConfig;
  private $encryptor;

  public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
  {
    $this->scopeConfig = $scopeConfig;
    $this->encryptor = $encryptor;
  }

  public function getEnvironment()
  {
    return $this->scopeConfig->getValue(self::CONFIG_PATH_ENVIRONMENT);
  }

  public function getTitle()
  {
    return $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE);
  }

  public function getClientId()
  {
    return $this->scopeConfig->getValue(self::CONFIG_PATH_CLIENT_ID);
  }

  public function getClientSecret()
  {
    $encryptedSecret = $this->scopeConfig->getValue(self::CONFIG_PATH_CLIENT_SECRET);
    return $this->encryptor->decrypt($encryptedSecret);
  }

  public function getOrderDescription()
  {
    return $this->scopeConfig->getValue(self::CONFIG_PATH_ORDER_DESCRIPTION);
  }
  public function getDestinationVaultHandle()
  {
    return $this->scopeConfig->getValue(self::CONFIG_VAULT_PREFIX . $this->getEnvironment());
  }
}
