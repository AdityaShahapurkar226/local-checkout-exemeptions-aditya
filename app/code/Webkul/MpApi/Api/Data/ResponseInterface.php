<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Api\Data;

/**
 * MpApi Device Token interface.
 *
 * @api
 */
interface ResponseInterface
{
    /**
     * Get response.
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getResponse();
}
