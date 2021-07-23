<?php

namespace AditumPayments\ApiSDK\Domains;

class Card {
    // Opcional
    private $brandName = "";

    // Obrigatório
    private $cardNumber = "";
    private $cvv = "";
    private $cardholderName = "";
    private $expirationMonth = "";
    private $expirationYear = "";
    private $cardholderDocument = "";

    public $address = NULL;
    public $billingAddress = NULL;

    public function __construct() {
        $this->address = new Address;
        $this->billingAddress = new Address;
    }

    public function setBrandName($brandName) {
        $this->brandName = $brandName;
    }

    public function getBrandName() {
        return $this->brandName;
    }

    public function setCardNumber($cardNumber) {
        $this->cardNumber = $cardNumber;
    }

    public function getCardNumber() {
        return $this->cardNumber;
    }

    public function setCVV($cvv) {
        $this->cvv = $cvv;
    }

    public function getCVV() {
        return $this->cvv;
    }

    public function setCardholderName($cardholderName) {
        $this->cardholderName = $cardholderName;
    }

    public function getCardholderName() {
        return $this->cardholderName;
    }
    
    public function setExpirationMonth($expirationMonth) {
        $this->expirationMonth = $expirationMonth;
    }

    public function getExpirationMonth() {
        return $this->expirationMonth;
    }

    public function setExpirationYear($expirationYear) {
        $this->expirationYear = $expirationYear;
    }

    public function getExpirationYear() {
        return $this->expirationYear;
    }

    public function setCardholderDocument($cardholderDocument) {
        $this->cardholderDocument = $cardholderDocument;
    }

    public function getCardholderDocument() {
        return $this->cardholderDocument;
    }
}