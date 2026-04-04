<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\ViewInterface;
use Amasty\Mostviewed\Api\ViewRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\ViewFactory;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\View as ViewResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewRepository implements ViewRepositoryInterface
{
    /**
     * @var ViewFactory
     */
    private $viewFactory;

    /**
     * @var ViewResource
     */
    private $viewResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $views;

    public function __construct(
        ViewFactory $viewFactory,
        ViewResource $viewResource
    ) {
        $this->viewFactory = $viewFactory;
        $this->viewResource = $viewResource;
    }

    /**
     * @inheritdoc
     */
    public function save(ViewInterface $view)
    {
        try {
            if ($view->getId()) {
                $view = $this->getById($view->getId())->addData($view->getData());
            }
            $this->viewResource->save($view);
            unset($this->views[$view->getId()]);
        } catch (\Exception $e) {
            if ($view->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save view with ID %1. Error: %2',
                        [$view->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new view. Error: %1', $e->getMessage()));
        }

        return $view;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->views[$id])) {
            /** @var \Amasty\Mostviewed\Model\View $view */
            $view = $this->viewFactory->create();
            $this->viewResource->load($view, $id);
            if (!$view->getId()) {
                throw new NoSuchEntityException(__('View with specified ID "%1" not found.', $id));
            }
            $this->views[$id] = $view;
        }

        return $this->views[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(ViewInterface $view)
    {
        try {
            $this->viewResource->delete($view);
            unset($this->views[$view->getId()]);
        } catch (\Exception $e) {
            if ($view->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove view with ID %1. Error: %2',
                        [$view->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove view. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $viewModel = $this->getById($id);
        $this->delete($viewModel);

        return true;
    }
}
