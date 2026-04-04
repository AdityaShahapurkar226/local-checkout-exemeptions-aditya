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
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Webkul\Marketplace\Model\SellerFactory;

class Categories extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{

    /**
     * @var \Magento\Framework\Registry
     */

    public $registry;

    /**
     * @var \Magento\Catalog\Model\Category
     */

    public $category;

    /**
     * @var \Magento\Catalog\Helper\Category
     */

    public $categoryHelper;

    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */

    public $sellerFactory;

    /**
     * @var array
     */
    protected $selectedIds  = [];

    /**
     * @var array
     */
    protected $expandedPath = [];

    public const ASSIGN_CATEGORY_TEMPLATE = 'customer/categories.phtml';

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param SellerFactory $sellerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        SellerFactory $sellerFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $categoryTree,
            $registry,
            $categoryFactory,
            $jsonEncoder,
            $resourceHelper,
            $backendSession,
            $data
        );
        $this->registry = $registry;
        $this->sellerFactory = $sellerFactory;
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate(self::ASSIGN_CATEGORY_TEMPLATE);
        return $this;
    }

    /**
     * GetSellerAllowedCategory
     *
     * @return string $category;
     */
    public function getSellerAllowedCategory()
    {
        $sellerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $seller = $this->sellerFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->setPageSize(1)
            ->getFirstItem();
        $category = "";
        if ($seller->getEntityId()) {
            $category = $seller->getAllowedCategories();
        }
        return $category;
    }

    /**
     * Get Category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $ids = $this->getSellerAllowedCategory();
       
        $categoryIds = explode(",", $ids);
        $this->selectedIds = $categoryIds;
        return $this->selectedIds;
    }

    /**
     * Set Category Ids
     *
     * @param array $ids
     * @return array
     */
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = [];
        } elseif (!is_array($ids)) {
            $ids = [(int)$ids];
        }
        $this->selectedIds = $ids;
        return $this;
    }

    /**
     * Get Expanded Path
     */
    protected function getExpandedPath()
    {
        return $this->expandedPath;
    }

    /**
     * Set Expanded Path
     *
     * @param mixed $path
     * @return mixed
     */
    protected function setExpandedPath($path)
    {
        $this->expandedPath = array_merge($this->expandedPath, explode("/", $path));
        return $this;
    }

    /**
     * Get Node Json
     *
     * @param mixed $node
     * @param int $level
     *
     * @return array
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
            // create a node from data array
        $item["text"] = $this->_escaper->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $item["text"] .= " (" . $node->getProductCount() . ")";
        }
        $item["id"] = $node->getId();
        $item["path"] = $node->getData("path");
        $item["cls"] = "folder " . ($node->getIsActive() ? "active-category" : "no-active-category");
        $item["allowDrop"] = false;
        $item["allowDrag"] = false;
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData("path"));
            $item["checked"] = true;
        }
        // if ($node->getLevel() < 2) {
        //     $this->setExpandedPath($node->getData("path"));
        // }
        if ($node->hasChildren()) {
            $item["children"] = [];
            foreach ($node->getChildren() as $child) {
                $item["children"][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($item["children"]) && (int)$node->getChildrenCount() > 0) {
            $item["children"] = [];
        }
        $item["expanded"] = in_array($node->getId(), $this->getExpandedPath());
        return $item;
    }

    /**
     * Get loader tree url
     *
     * @param bool|null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        $queryParams = ['customer_id' => $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)];
        return $this->getUrl(
            'marketplace/category/categoriesJson',
            ['_query' => $queryParams]
        );
    }
    /**
     * Get Cat Ids
     */
    public function getCatIds()
    {
        return $this->getSellerAllowedCategory();
    }

    /**
     * To hide root category
     *
     * @return bool
     */
    public function getIsRootCatToShow()
    {
        return false;
    }

    /**
     * Get root category for tree
     *
     * @param mixed|null $parentCategoryNode
     * @param int $recursionLevel
     * @return Node|array|null
     */
    public function getRoot($parentCategoryNode = null, $recursionLevel = 3)
    {
        if ($parentCategoryNode !== null && $parentCategoryNode->getId()) {
            return $this->getNode($parentCategoryNode, $recursionLevel);
        }
        $root = $this->registry->registry('root');
        if ($root === null) {
            $currentStoreId = (int)$this->getRequest()->getParam('store');
            $currentRootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            if ($currentStoreId) {
                $currentStore = $this->_storeManager->getStore($currentStoreId);
                $currentRootId = $currentStore->getRootCategoryId();
            }

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes(
                    $this->getCategory(),
                    $tree->getNodeById($currentRootId)
                );
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($currentRootId);

            if ($root) {
                $catIds = $this->getCategoryIds();
                $isChecked = in_array($currentRootId, $catIds);
                $root->setIsVisible(true);
                if ($root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    $root->setName(__('Root'));
                    $root->setChecked($isChecked);
                }
            }

            $this->registry->register('root', $root);
        }

        return $root;
    }
}
