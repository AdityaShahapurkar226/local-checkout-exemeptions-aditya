<?php

namespace Bitrail\HyvaCheckout\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class ClientSecret extends Value
{
  private $encryptor;

  public function __construct(
    Context $context,
    Registry $registry,
    ScopeConfigInterface $config,
    TypeListInterface $cacheTypeList,
    EncryptorInterface $encryptor,
    AbstractResource $resource = null,
    AbstractDb $resourceCollection = null,
    array $data = []
  ) {
    $this->encryptor = $encryptor;

    parent::__construct(
      $context,
      $registry,
      $config,
      $cacheTypeList,
      $resource,
      $resourceCollection,
      $data
    );
  }

  public function beforeSave()
  {
    $secret = $this->getValue();

    if (!$this->isEncrypted($secret)) {
      $encryptedSecret = $this->encryptor->encrypt($secret);
      $this->setValue($encryptedSecret);
    }

    return parent::beforeSave();
  }

  private function isEncrypted($value)
  {
    return preg_match('/^0:\d+:.{125}=$/', $value);
  }
}
