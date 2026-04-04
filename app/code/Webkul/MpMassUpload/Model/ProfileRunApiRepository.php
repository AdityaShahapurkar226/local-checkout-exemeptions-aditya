<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Model;

use Webkul\MpMassUpload\Api\ProfileRunApiRepositoryInterface;
use Webkul\MpMassUpload\Model\ResourceModel\ProfileRunApi\CollectionFactory;
use Webkul\MpMassUpload\Model\ProfileRunApiFactory;

/**
 * ProfileRunApiRepository Class CURD
 */
class ProfileRunApiRepository implements ProfileRunApiRepositoryInterface
{
    /**
     * @var ProfileRunApiFactory $modelFactory
     */
    protected $modelFactory = null;
    
    /**
     *
     * @var CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * @param ProfileRunApiFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ProfileRunApiFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpMassUpload\Model\ProfileRunApi
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
     * @param \Webkul\MpMassUpload\Model\ProfileRunApi $subject
     * @return \Webkul\MpMassUpload\Model\ProfileRunApi
     */
    public function save(\Webkul\MpMassUpload\Model\ProfileRunApi $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
         return $subject;
    }

    /**
     * Get list
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
     * @param \Webkul\MpMassUpload\Model\ProfileRunApi $subject
     * @return boolean
     */
    public function delete(\Webkul\MpMassUpload\Model\ProfileRunApi $subject)
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
