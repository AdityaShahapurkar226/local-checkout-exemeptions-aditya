<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpAssignProduct
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Logger;

/**
 *  MpAssignProduct Handler class.
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/assignproduct.log';
}
