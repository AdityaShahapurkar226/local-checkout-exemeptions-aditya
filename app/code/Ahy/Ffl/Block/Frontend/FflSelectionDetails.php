<?php
namespace Ahy\Ffl\Block\Frontend;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class FflSelectionDetails extends Template
{
    protected $selectedFflCentreId;
    protected $selectedOption;
    protected $agreeOnTermAndCondition;
    protected $selectedFflCentreName;
    protected $selectedFflCentreAddressHtml;

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function setSelectedFflCentreId($selectedFflCentreId)
    {
        $this->selectedFflCentreId = $selectedFflCentreId;
    }

    public function setSelectedOption($selectedOption)
    {
        $this->selectedOption = $selectedOption;
    }

    public function setAgreeOnTermAndCondition($agreeOnTermAndCondition)
    {
        $this->agreeOnTermAndCondition = $agreeOnTermAndCondition;
    }

    public function setSelectedFflCentreName($selectedFflCentreName)
    {
        $this->selectedFflCentreName = $selectedFflCentreName;
    }

    public function setSelectedFflCentreAddressHtml($selectedFflCentreAddressHtml)
    {
        $this->selectedFflCentreAddressHtml = $selectedFflCentreAddressHtml;
    }

    public function getSelectedFflCentreId()
    {
        return $this->selectedFflCentreId;
    }

    public function getSelectedOption()
    {
        return $this->selectedOption;
    }

    public function getAgreeOnTermAndCondition()
    {
        return $this->agreeOnTermAndCondition;
    }

    public function getSelectedFflCentreName()
    {
        return $this->selectedFflCentreName;
    }

    public function getSelectedFflCentreAddressHtml()
    {
        return $this->selectedFflCentreAddressHtml;
    }
}
