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
namespace Webkul\SellerSubAccount\Controller\Mui;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Controller\UiActionInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Render
 */
abstract class AbstractAction extends Action implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     */
    public $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     */
    public function __construct(Context $context, UiComponentFactory $factory)
    {
        parent::__construct($context);
        $this->factory = $factory;
    }

    /**
     * Getting name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_request->getParam('name');
    }

    /**
     * Getting component
     *
     * @return mixed
     */
    public function getComponent()
    {
        return $this->_request->getParam('component');
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }

    /**
     * Is Allowed
     *
     * @return void
     */
    public function _isAllowed()
    {
        return true;
    }
}
