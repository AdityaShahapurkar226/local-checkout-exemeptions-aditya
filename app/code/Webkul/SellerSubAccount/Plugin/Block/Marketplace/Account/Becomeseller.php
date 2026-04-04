<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Plugin\Block\Marketplace\Account;
 
class Becomeseller
{
    /**
     * @var \Webkul\SellerSubAccount\Helper\Data
     */
    public $_helperData;

    /**
     * @param Webkul\SellerSubAccount\Helper\Data $helperData
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * Before To Html
     *
     * @param \Webkul\Marketplace\Block\Account\Becomeseller $subject
     *
     * @return void
     */
    public function beforeToHtml(\Webkul\Marketplace\Block\Account\Becomeseller $subject)
    {
        $template = $subject->getTemplate();
        if ($template === 'account/becomeseller.phtml' && !$this->_helperData->isAllowForBecomeSeller()) {
            $subject->setTemplate('Webkul_SellerSubAccount::account/becomeseller.phtml');
        }
    }
}
