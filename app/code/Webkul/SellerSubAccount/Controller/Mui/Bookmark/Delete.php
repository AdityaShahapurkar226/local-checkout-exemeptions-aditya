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

namespace Webkul\SellerSubAccount\Controller\Mui\Bookmark;

/**
 * Class Delete action
 */
class Delete extends \Webkul\SellerSubAccount\Controller\Mui\AbstractAction
{
    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepositoryInterface;

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface
     */
    private $bookmarkManagementInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $factory
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepositoryInterface
     * @param \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagementInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\UiComponentFactory $factory,
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepositoryInterface,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagementInterface
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepositoryInterface = $bookmarkRepositoryInterface;
        $this->bookmarkManagementInterface = $bookmarkManagementInterface;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $viewIds = explode('.', $this->_request->getParam('data'));
        $bookmark = $this->bookmarkManagementInterface->getByIdentifierNamespace(
            array_pop($viewIds),
            $this->_request->getParam('namespace')
        );

        if ($bookmark && $bookmark->getId()) {
            $this->bookmarkRepositoryInterface->delete($bookmark);
        }
    }
}
