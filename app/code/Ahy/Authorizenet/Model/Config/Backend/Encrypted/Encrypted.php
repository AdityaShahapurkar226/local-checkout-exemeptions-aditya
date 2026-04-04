<?php
namespace Ahy\Authorizenet\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Encrypted as MagentoEncrypted;

class Encrypted extends MagentoEncrypted
{
    /**
     * Retrieve decrypted value
     *
     * @return string
     */
    public function getDecryptedValue()
    {
        $value = $this->getValue();
        if ($value && $this->isEncrypted($value)) {
            $value = $this->getEncryptor()->decrypt($value);
        }
        return $value;
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value && !$this->isEncrypted($value)) {
            $this->setValue($this->getEncryptor()->encrypt($value));
        }
    }
}
