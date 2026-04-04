<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_SellerSubAccount
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int $loggerType
     */
    public $loggerType = SellerSubAccountLogger::CRITICAL;

    /**
     * @var string $fileName
     */
    public $fileName = '/var/log/SellerSubAccount.log';
}
