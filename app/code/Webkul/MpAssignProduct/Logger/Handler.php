<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpAssignProduct
 * @author     Webkul
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Logger;

/**
 *  MpAssignProduct Handler class.
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/assignproduct.log';
}
