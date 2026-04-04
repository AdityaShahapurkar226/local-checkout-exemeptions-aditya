<?php

namespace Ahy\ThemeCustomization\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class RelatedCategory extends Template
{
    protected $registry;
    protected $_directoryList;

    public function __construct(Context $context, Registry $registry, DirectoryList $directoryList, array $data = [])
    {
        $this->registry = $registry;
        $this->_directoryList = $directoryList;
        parent::__construct($context, $data);
    }


    public function getCategoryIds()
    {
        $product = $this->registry->registry('current_product');
        return $product ? $product->getCategoryIds() : [];
    }

    public function getFirstCategoryAndChildren()
    {
        $categoryIds = $this->getCategoryIds();
        if (!empty($categoryIds) && array_key_exists(0, $categoryIds)) {
            $firstCategoryId = $categoryIds[0];
        } else {
            // Handle the case where the array is empty or the key doesn't exist
            $firstCategoryId = null; // or some default value
        }

        $pubPath = $this->_directoryList->getPath(DirectoryList::PUB);
        $json = file_get_contents($pubPath . '/categories.json');
        $categories = json_decode($json, true);

        $category = $this->_findCategoryById($categories, $firstCategoryId);

        $result = [];

        if (isset($category['children'])) {
            $i = 0;
            foreach ($category['children'] as $child) {
                if ($i >= 3) {
                    break;
                }

                $result[] = [
                    'name' => $child['name'],
                    'url' => $child['url']
                ];

                $i++;
            }
        } else {
            // If no children are found, let's try to get up to 3 siblings.
            $siblingCategories = $this->_findSiblingCategories($categories, $firstCategoryId);
            foreach($siblingCategories as $sibling){
                if(count($result) >= 3){
                    break;
                }

                $result[] = [
                    'name' => $sibling['name'],
                    'url' => $sibling['url']
                ];
            }
        }

        return $result;
    }

    private function _findSiblingCategories($categories, $id)
    {
        foreach ($categories as $category) {
            if (isset($category['children'])) {
                foreach ($category['children'] as $child) {
                    if ($child['category_id'] == $id) {
                        return $category['children'];
                    }

                    $result = $this->_findSiblingCategories($category['children'], $id);

                    if ($result) {
                        return $result;
                    }
                }
            }
        }

        return [];
    }


    private function _findCategoryById($categories, $id)
    {
        foreach ($categories as $category) {
            if ($category['category_id'] == $id) {
                return $category;
            }

            if (isset($category['children'])) {
                $result = $this->_findCategoryById($category['children'], $id);

                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

}
