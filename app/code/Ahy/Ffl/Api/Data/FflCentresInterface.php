<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api\Data;

interface FflCentresInterface
{

    const EMAIL = 'email';
    const ADDRESSLINE2 = 'AddressLine2';
    const PHONE_NO = 'phone_no';
    const STATUS = 'status';
    const FFLCENTRES_ID = 'fflcentres_id';
    const LAT = 'lat';
    const CITY = 'City';
    const CENTRENAME = 'CentreName';
    const REGION_ID = 'region_id';
    const LONG = 'long';
    const ADDRESSLINE1 = 'AddressLine1';
    const ZIPCODE = 'zipcode';
    const WORKING_TIME = 'working_time';
    const STATE = 'State';
    const TRANSFER_FEE = 'transfer_fee';
    const CREATED_AT = 'created_at';
    const FAX = 'fax';
    const COUNTRY_ID = 'country_id';

    /**
     * Get fflcentres_id
     * @return string|null
     */
    public function getFflcentresId();

    /**
     * Set fflcentres_id
     * @param string $fflcentresId
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setFflcentresId($fflcentresId);

    /**
     * Get CentreName
     * @return string|null
     */
    public function getCentreName();

    /**
     * Set CentreName
     * @param string $centreName
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setCentreName($centreName);

    /**
     * Get State
     * @return string|null
     */
    public function getState();

    /**
     * Set State
     * @param string $state
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setState($state);

    /**
     * Get AddressLine1
     * @return string|null
     */
    public function getAddressLine1();

    /**
     * Set AddressLine1
     * @param string $addressLine1
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setAddressLine1($addressLine1);

    /**
     * Get AddressLine2
     * @return string|null
     */
    public function getAddressLine2();

    /**
     * Set AddressLine2
     * @param string $addressLine2
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setAddressLine2($addressLine2);

    /**
     * Get City
     * @return string|null
     */
    public function getCity();

    /**
     * Set City
     * @param string $city
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setCity($city);

    /**
     * Get region_id
     * @return string|null
     */
    public function getRegionId();

    /**
     * Set region_id
     * @param string $regionId
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setRegionId($regionId);

    /**
     * Get country_id
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set country_id
     * @param string $countryId
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setCountryId($countryId);

    /**
     * Get phone_no
     * @return string|null
     */
    public function getPhoneNo();

    /**
     * Set phone_no
     * @param string $phoneNo
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setPhoneNo($phoneNo);

    /**
     * Get fax
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax
     * @param string $fax
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setFax($fax);

    /**
     * Get working_time
     * @return string|null
     */
    public function getWorkingTime();

    /**
     * Set working_time
     * @param string $workingTime
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setWorkingTime($workingTime);

    /**
     * Get transfer_fee
     * @return string|null
     */
    public function getTransferFee();

    /**
     * Set transfer_fee
     * @param string $transferFee
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setTransferFee($transferFee);

    /**
     * Get lat
     * @return string|null
     */
    public function getLat();

    /**
     * Set lat
     * @param string $lat
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setLat($lat);

    /**
     * Get long
     * @return string|null
     */
    public function getLong();

    /**
     * Set long
     * @param string $long
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setLong($long);

    /**
     * Get zipcode
     * @return string|null
     */
    public function getZipcode();

    /**
     * Set zipcode
     * @param string $zipcode
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setZipcode($zipcode);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Ahy\Ffl\FflCentres\Api\Data\FflCentresInterface
     */
    public function setEmail($email);
}

