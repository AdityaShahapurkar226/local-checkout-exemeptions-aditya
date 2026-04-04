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

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;

use Magento\Framework\App\Action\Action;
use Magento\Ui\Controller\UiActionInterface;

/**
 * Class Bookmark Save action
 */
class Save extends Action implements UiActionInterface, HttpPostActionInterface
{
    /**
     * Identifier for current bookmark
     */
    public const CURRENT_IDENTIFIER = 'current';

    public const ACTIVE_IDENTIFIER = 'activeIndex';

    public const VIEWS_IDENTIFIER = 'views';

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $uiBookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    protected $uiBookmarkManagement;

    /**
     * @var BookmarkInterfaceFactory
     */
    protected $sellerBookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $uiBookmarkRepository
     * @param BookmarkManagementInterface $uiBookmarkManagement
     * @param BookmarkInterfaceFactory $sellerBookmarkFactory
     * @param UserContextInterface $userContext
     * @param DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $uiBookmarkRepository,
        BookmarkManagementInterface $uiBookmarkManagement,
        BookmarkInterfaceFactory $sellerBookmarkFactory,
        UserContextInterface $userContext,
        DecoderInterface $jsonDecoder,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context);
        $this->factory = $factory;
        $this->uiBookmarkRepository = $uiBookmarkRepository;
        $this->uiBookmarkManagement = $uiBookmarkManagement;
        $this->bookmarkFactory = $sellerBookmarkFactory;
        $this->userContext = $userContext;
        $this->jsonDecoder = $jsonDecoder;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function execute()
    {
        if (!$this->userContext->getUserId()) {
            return;
        }

        $sellerBookmark = $this->bookmarkFactory->create();
        $jsonData = $this->_request->getParam('data');
        if (!$jsonData) {
            throw new \InvalidArgumentException('Invalid parameter "data"');
        }
        $data = $this->serializer->unserialize($jsonData);
        $action = key($data);
        switch ($action) {
            case self::ACTIVE_IDENTIFIER:
                $this->updateCurrentBookmark($data[$action]);
                break;

            case self::CURRENT_IDENTIFIER:
                $this->updateBookmark(
                    $sellerBookmark,
                    $action,
                    $sellerBookmark->getTitle(),
                    $jsonData
                );

                break;

            case self::VIEWS_IDENTIFIER:
                foreach ($data[$action] as $identifier => $data) {
                    $this->updateBookmark(
                        $sellerBookmark,
                        $identifier,
                        isset($data['label']) ? $data['label'] : '',
                        $jsonData
                    );
                    $this->updateCurrentBookmark($identifier);
                }

                break;

            default:
                throw new \LogicException(__('Unsupported bookmark action.'));
        }
    }

    /**
     * Update bookmarks based on request params
     *
     * @param BookmarkInterface $sellerBookmark
     * @param string $identifier
     * @param string $title
     * @param string $config
     * @return void
     */
    protected function updateBookmark(BookmarkInterface $sellerBookmark, $identifier, $title, $config)
    {
        $updateBookmark = $this->checkBookmark($identifier);
        if ($updateBookmark !== false) {
            $sellerBookmark = $updateBookmark;
        }

        $sellerBookmark->setUserId($this->userContext->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config);
        $this->uiBookmarkRepository->save($sellerBookmark);
    }

    /**
     * Update current bookmark
     *
     * @param string $identifier
     * @return void
     */
    protected function updateCurrentBookmark($identifier)
    {
        $sellerBookmarks = $this->uiBookmarkManagement->loadByNamespace($this->_request->getParam('namespace'));
        foreach ($sellerBookmarks->getItems() as $sellerBookmark) {
            if ($sellerBookmark->getIdentifier() == $identifier) {
                $sellerBookmark->setCurrent(true);
            } else {
                $sellerBookmark->setCurrent(false);
            }
            $this->uiBookmarkRepository->save($sellerBookmark);
        }
    }

    /**
     * Check bookmark by identifier
     *
     * @param string $identifier
     * @return bool|BookmarkInterface
     */
    protected function checkBookmark($identifier)
    {
        $result = false;

        $updateBookmark = $this->uiBookmarkManagement->getByIdentifierNamespace(
            $identifier,
            $this->_request->getParam('namespace')
        );

        if ($updateBookmark) {
            $result = $updateBookmark;
        }

        return $result;
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
