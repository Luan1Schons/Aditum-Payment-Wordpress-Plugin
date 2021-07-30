<?php

namespace AditumPayments\ApiSDK\Domains;

class Address {
    private $street = "";
    private $number = "";
    private $neighborhood = "";
    private $city = "";
    private $state = "";
    private $country = "";
    private $zipcode = "";
    private $complement = "";

    public function setStreet($street) {
        $this->street = $street;
    }

    public function getStreet() {
        return $this->street;
    }

    public function setNumber($number) {
        $this->number = $number;
    }

    public function getNumber() {
        return $this->number;
    }

    public function setNeighborhood($neighborhood) {
        $this->neighborhood = $neighborhood;
    }

    public function getNeighborhood() {
        return $this->neighborhood;
    }

    public function setCity($city) {
        $this->city = $city;
    }

    public function getCity() {
        return $this->city;
    }

    public function setState($state) {
        $this->state = $state;
    }

    public function getState() {
        return $this->state;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    public function getCountry() {
        return $this->country;
    }

    public function setZipcode($zipcode) {
        $this->zipcode = $zipcode;
    }

    public function getZipcode() {
        return $this->zipcode;
    }

    public function setComplement($complement) {
        $this->complement = $complement;
    }

    public function getComplement() {
        return $this->complement;
    }
}