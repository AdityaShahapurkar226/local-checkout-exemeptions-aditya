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
namespace Webkul\MpMassUpload\Api\Data;

/**
 * MpMassUpload ResponseInterface interface.
 *
 * @api
 */
interface ResponseInterface
{
    /**
     * Get response.
     *
     * @return Webkul\MpMassUpload\Api\Data\ResponseInterface
     */
    public function getResponse();
}
