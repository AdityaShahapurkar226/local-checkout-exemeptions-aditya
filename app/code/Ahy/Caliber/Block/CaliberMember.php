<?php
/**
 * Copyright © Ahy consulting All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Caliber\Block;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupRepositoryInterface;
use \Magento\Framework\View\Element\Template\Context;
use Ahy\Caliber\Service\GetCaliberMemberCustomerDetails as CaliberMemberDetails;

class CaliberMember extends \Magento\Framework\View\Element\Template
{

    const CUSTOMER_GROUP_ID = 6;
    protected $customerSession;
    protected $groupRepository;
    protected $caliberMemberDetails;

    /**
     * Constructor
     *
     * @param Context  $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        GroupRepositoryInterface $groupRepository,
        CaliberMemberDetails $caliberMemberDetails,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->caliberMemberDetails = $caliberMemberDetails;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId(): int 
    {
        return self::CUSTOMER_GROUP_ID;
    }

    /**
     * @return bool
     */
    public function isCaliberMember(): bool
    {
        $groupId = $this->customerSession->getCustomer()->getGroupId();
        if($groupId == $this->getCustomerGroupId()){
            return true;
        }
        return false;
    }

    public function getUserEmail(): string
    {
        $customer = $this->customerSession->getCustomer();
        $email = $customer->getEmail();
        return $email;
    }

    public function getCaliberMemberDetails(): ?array
    {
        $customerEmail = $this->getUserEmail();
        return $this->caliberMemberDetails->getCustomerDetails($customerEmail);
    }

    public function getBalancePoints(): ?int
    {
        try {
            $customerDetails = $this->getCaliberMemberDetails();
            $pointsBalance = $customerDetails['points_balance'];
            // Use the $pointsBalance variable as needed
            return $pointsBalance;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return null;
    }



}

