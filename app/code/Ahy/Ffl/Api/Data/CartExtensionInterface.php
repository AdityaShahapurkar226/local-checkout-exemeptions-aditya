<?php
    namespace Ahy\Ffl\Api\Data;

    interface CartExtensionInterface extends \Magento\Quote\Api\Data\CartExtensionInterface
    {
        /**
         * @return bool|null
         */
        public function getAgeVerified();
        /**
         * @param bool $ageVerified
         * @return $this
         */
        public function setAgeVerified($ageVerified);
        /**
         * @return int|null
         */
        public function getAgeOfPurchaser();
        /**
         * @param int $ageOfPurchaser
         * @return $this
         */
        public function setAgeOfPurchaser($ageOfPurchaser);
        /**
         * @return string|null
         */
        public function getFflCentre();
        /**
         * @param string $fflCentre
         * @return $this
         */
        public function setFflCentre($fflCentre);
    }
?>