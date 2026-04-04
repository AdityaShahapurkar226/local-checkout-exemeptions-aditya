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

use Magento\Ui\Api\Data\BookmarkInterface;

/**
 * Class Save action : to save bookmarks
 *
 */
class Save extends \Webkul\SellerSubAccount\Controller\Mui\AbstractAction
{
    /**
     * Identifier for current bookmark
     */
    public const CURRENT_IDENTIFIER = 'current';

    public const ACTIVE_IDENTIFIER = 'activeIndex';

    public const VIEWS_IDENTIFIER = 'views';

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepositoryInterface;

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface
     */
    protected $bookmarkManagementInterface;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkInterfaceFactory
     */
    protected $bookmarkInterfaceFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContextInterface;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoderInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $factory
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepositoryInterface
     * @param \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagementInterface
     * @param \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkInterfaceFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContextInterface
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoderInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\UiComponentFactory $factory,
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepositoryInterface,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagementInterface,
        \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkInterfaceFactory,
        \Magento\Authorization\Model\UserContextInterface $userContextInterface,
        \Magento\Framework\Json\DecoderInterface $jsonDecoderInterface
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepositoryInterface = $bookmarkRepositoryInterface;
        $this->bookmarkManagementInterface = $bookmarkManagementInterface;
        $this->bookmarkInterfaceFactory = $bookmarkInterfaceFactory;
        $this->userContextInterface = $userContextInterface;
        $this->jsonDecoderInterface = $jsonDecoderInterface;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $bookmark = $this->bookmarkInterfaceFactory->create();
        $jsonParamData = $this->_request->getParam('data');
        if (!$jsonParamData) {
            throw new \InvalidArgumentException('Invalid parameter "data"');
        }
        $decodedData = $this->jsonDecoderInterface->decode($jsonParamData);
        $action = key($decodedData);
        switch ($action) {
            case self::ACTIVE_IDENTIFIER:
                $this->updateCurrentBookmark($decodedData[$action]);
                break;

            case self::CURRENT_IDENTIFIER:
                $this->updateBookmark(
                    $bookmark,
                    $action,
                    $bookmark->getTitle(),
                    $jsonParamData
                );

                break;

            case self::VIEWS_IDENTIFIER:
                foreach ($decodedData[$action] as $identifier => $decodedData) {
                    $this->updateBookmark(
                        $bookmark,
                        $identifier,
                        isset($decodedData['label']) ? $decodedData['label'] : '',
                        $jsonParamData
                    );
                    $this->updateCurrentBookmark($identifier);
                }

                break;

            default:
                throw new \LogicException(
                    __('Unsupported bookmark action.')
                );
        }
    }

    /**
     * Update bookmarks based on request params
     *
     * @param BookmarkInterface $bookmark
     * @param string $identifier
     * @param string $title
     * @param string $config
     * @return void
     */
    protected function updateBookmark(BookmarkInterface $bookmark, $identifier, $title, $config)
    {
        $updateBookmark = $this->checkBookmark($identifier);
        if ($updateBookmark !== false) {
            $bookmark = $updateBookmark;
        }

        $bookmark->setUserId($this->userContextInterface->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config);
        $this->bookmarkRepositoryInterface->save($bookmark);
    }

    /**
     * Update current bookmark
     *
     * @param string $identifier
     * @return void
     */
    protected function updateCurrentBookmark($identifier)
    {
        $bookmarks = $this->bookmarkManagementInterface->loadByNamespace(
            $this->_request->getParam('namespace')
        );
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() == $identifier) {
                $bookmark->setCurrent(true);
            } else {
                $bookmark->setCurrent(false);
            }
            $this->bookmarkRepositoryInterface->save($bookmark);
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

        $updateBookmark = $this->bookmarkManagementInterface->getByIdentifierNamespace(
            $identifier,
            $this->_request->getParam('namespace')
        );

        if ($updateBookmark) {
            $result = $updateBookmark;
        }

        return $result;
    }
}
