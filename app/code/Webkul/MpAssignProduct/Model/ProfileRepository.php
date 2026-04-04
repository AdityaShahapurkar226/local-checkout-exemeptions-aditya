<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpAssignProduct\Model;

/**
 * MpAssignproduct profileRepository Class
 */
class ProfileRepository implements \Webkul\MpAssignProduct\Api\ProfileRepositoryInterface
{

    /**
     *
     * @var [type]
     */
    protected $modelFactory = null;

    /**
     *
     * @var [type]
     */
    protected $collectionFactory = null;

    /**
     * initialize
     *
     * @param Webkul\MpAssignProduct\Model\ProfileFactory $modelFactory
     * @param Webkul\MpAssignProduct\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        \Webkul\MpAssignProduct\Model\ProfileFactory $modelFactory,
        \Webkul\MpAssignProduct\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Get by id
     *
     * @param int $subject
     * @return Webkul\MpAssignProduct\Model\Profile
     */
    public function save(\Webkul\MpAssignProduct\Model\Profile $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
         return $subject;
    }

    /**
     * Gset list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        $collection = $this->collectionFactory->create();
         return $collection;
    }

    /**
     * Delete
     *
     * @param Webkul\MpAssignProduct\Model\Profile $subject
     * @return boolean
     */
    public function delete(\Webkul\MpAssignProduct\Model\Profile $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
