<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Mui\Bookmark;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

use Magento\Framework\App\Action\Action;
use Magento\Ui\Controller\UiActionInterface;

/**
 * Class Bookmark Delete action
 */
class Delete extends Action implements UiActionInterface
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $uiBookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    private $uiBookmarkManagement;

    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $uiBookmarkRepository
     * @param BookmarkManagementInterface $uiBookmarkManagement
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $uiBookmarkRepository,
        BookmarkManagementInterface $uiBookmarkManagement
    ) {
        parent::__construct($context);
        $this->factory = $factory;
        $this->uiBookmarkRepository = $uiBookmarkRepository;
        $this->uiBookmarkManagement = $uiBookmarkManagement;
    }

    /**
     * Getting component
     *
     * @return mixed
     */
    protected function getComponent()
    {
        return $this->_request->getParam('component');
    }

    /**
     * Getting name
     *
     * @return mixed
     */
    protected function getName()
    {
        return $this->_request->getParam('name');
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
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $viewIds = explode('.', $this->_request->getParam('data'));
        $sellerBookmark = $this->uiBookmarkManagement->getByIdentifierNamespace(
            array_pop($viewIds),
            $this->_request->getParam('namespace')
        );

        if ($sellerBookmark && $sellerBookmark->getId()) {
            $this->uiBookmarkRepository->delete($sellerBookmark);
        }
    }

    /**
     * Is Allowed Method
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
