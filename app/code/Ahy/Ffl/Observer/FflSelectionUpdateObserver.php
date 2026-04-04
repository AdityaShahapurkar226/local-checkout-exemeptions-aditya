<?php

namespace Ahy\Ffl\Observer;

use Ahy\Ffl\Block\Frontend\FflSelectionDetails;
use Ahy\Ffl\Magewire\HyvaCheckout\AhyFflSelection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class FflSelectionUpdateObserver implements ObserverInterface
{
    protected $logger;
    protected $fflSelectionDetailsBlock;

    public function __construct(
        LoggerInterface $logger,
        FflSelectionDetails $fflSelectionDetailsBlock
    ) {
        $this->logger = $logger;
        $this->fflSelectionDetailsBlock = $fflSelectionDetailsBlock;
    }

    public function execute(Observer $observer)
    {
        $this->logger->debug('Event dispatched: ahy_ffl_selection_update');

        /** @var AhyFflSelection $ahyFflSelection */
        $ahyFflSelection = $observer->getData('ahy_ffl_selection');

        // Update the values in the block
        $this->fflSelectionDetailsBlock->setSelectedFflCentreId($ahyFflSelection->selectedFflCentreId);
        $this->fflSelectionDetailsBlock->setSelectedOption($ahyFflSelection->selectedOption);
        $this->fflSelectionDetailsBlock->setAgreeOnTermAndCondition($ahyFflSelection->agreeOnTermAndCondition);
        $this->fflSelectionDetailsBlock->setSelectedFflCentreName($ahyFflSelection->selectedFflCentreName);
        $this->fflSelectionDetailsBlock->setSelectedFflCentreAddressHtml($ahyFflSelection->selectedFflCentreAddressHtml);

        // Log the values to verify they are being set correctly
        $this->logger->debug('Selected FFL Centre ID: ' . $ahyFflSelection->selectedFflCentreId);
        $this->logger->debug('Selected Option: ' . $ahyFflSelection->selectedOption);
        $this->logger->debug('Agree on Terms and Conditions: ' . $ahyFflSelection->agreeOnTermAndCondition);
        $this->logger->debug('Selected FFL Centre Name: ' . $ahyFflSelection->selectedFflCentreName);
        $this->logger->debug('Selected FFL Centre Address HTML: ' . $ahyFflSelection->selectedFflCentreAddressHtml);

        // Dispatch a custom JavaScript event to update the frontend in real time
        // $eventData = [
        //     'selectedFflCentreId' => $ahyFflSelection->selectedFflCentreId,
        //     // Include any other relevant data that needs to be updated on the frontend
        // ];
        // $this->fflSelectionDetailsBlock->dispatchBrowserEvent('ahyFflSelectionUpdated', $eventData);
    }
}
