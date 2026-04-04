<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\ViewModel;

use Webkul\Marketplace\Helper\Data as MpHelper;

class Upload implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var MpHelper
     */
    public $mpHelper;

    /**
     * @param MpHelper $mpHelper
     */
    public function __construct(
        MpHelper $mpHelper
    ) {
        $this->mpHelper = $mpHelper;
    }

    /**
     * IsSeller function
     *
     * @return boolean
     */
    public function isSeller()
    {
        return $this->mpHelper->isSeller();
    }
}
