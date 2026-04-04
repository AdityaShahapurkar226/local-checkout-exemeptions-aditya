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
namespace Webkul\MpMassUpload\Model;

use Webkul\MpMassUpload\Api\Data\ResponseInterface;

class Response extends \Magento\Framework\DataObject implements ResponseInterface
{

    /**
     * Prepare api response .
     *
     * @return \Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getResponse()
    {
        $data = $this->_data;
        return $data;
    }
}
