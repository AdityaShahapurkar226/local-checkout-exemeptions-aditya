<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model;

use Ahy\Ffl\Api\Data\FflCentresInterface;
use Magento\Framework\Model\AbstractModel;

class FflCentres extends AbstractModel implements FflCentresInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Ahy\Ffl\Model\ResourceModel\FflCentres::class);
    }

    /**
     * @inheritDoc
     */
    public function getFflcentresId()
    {
        return $this->getData(self::FFLCENTRES_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFflcentresId($fflcentresId)
    {
        return $this->setData(self::FFLCENTRES_ID, $fflcentresId);
    }

    /**
     * @inheritDoc
     */
    public function getCentreName()
    {
        return $this->getData(self::CENTRENAME);
    }

    /**
     * @inheritDoc
     */
    public function setCentreName($centreName)
    {
        return $this->setData(self::CENTRENAME, $centreName);
    }

    /**
     * @inheritDoc
     */
    public function getState()
    {
        return $this->getData(self::STATE);
    }

    /**
     * @inheritDoc
     */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * @inheritDoc
     */
    public function getAddressLine1()
    {
        return $this->getData(self::ADDRESSLINE1);
    }

    /**
     * @inheritDoc
     */
    public function setAddressLine1($addressLine1)
    {
        return $this->setData(self::ADDRESSLINE1, $addressLine1);
    }

    /**
     * @inheritDoc
     */
    public function getAddressLine2()
    {
        return $this->getData(self::ADDRESSLINE2);
    }

    /**
     * @inheritDoc
     */
    public function setAddressLine2($addressLine2)
    {
        return $this->setData(self::ADDRESSLINE2, $addressLine2);
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritDoc
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritDoc
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @inheritDoc
     */
    public function getPhoneNo()
    {
        return $this->getData(self::PHONE_NO);
    }

    /**
     * @inheritDoc
     */
    public function setPhoneNo($phoneNo)
    {
        return $this->setData(self::PHONE_NO, $phoneNo);
    }

    /**
     * @inheritDoc
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * @inheritDoc
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * @inheritDoc
     */
    public function getWorkingTime()
    {
        return $this->getData(self::WORKING_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setWorkingTime($workingTime)
    {
        return $this->setData(self::WORKING_TIME, $workingTime);
    }

    /**
     * @inheritDoc
     */
    public function getTransferFee()
    {
        return $this->getData(self::TRANSFER_FEE);
    }

    /**
     * @inheritDoc
     */
    public function setTransferFee($transferFee)
    {
        return $this->setData(self::TRANSFER_FEE, $transferFee);
    }

    /**
     * @inheritDoc
     */
    public function getLat()
    {
        return $this->getData(self::LAT);
    }

    /**
     * @inheritDoc
     */
    public function setLat($lat)
    {
        return $this->setData(self::LAT, $lat);
    }

    /**
     * @inheritDoc
     */
    public function getLong()
    {
        return $this->getData(self::LONG);
    }

    /**
     * @inheritDoc
     */
    public function setLong($long)
    {
        return $this->setData(self::LONG, $long);
    }

    /**
     * @inheritDoc
     */
    public function getZipcode()
    {
        return $this->getData(self::ZIPCODE);
    }

    /**
     * @inheritDoc
     */
    public function setZipcode($zipcode)
    {
        return $this->setData(self::ZIPCODE, $zipcode);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }
}

