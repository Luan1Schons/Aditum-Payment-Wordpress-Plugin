<?php

namespace AditumPayments\ApiSDK\Domains;

use AditumPayments\ApiSDK\Enum\AcquirerCode;

class Transactions {

    private $amount = "";
    private $paymentType = "";
    private $installmentNumber = 1;
    private $instructions = "";
    
    public $card = NULL;
    public $fine = NULL;
    public $discount = NULL;

    public function __construct() {
        $this->card = new Card;
        $this->fine = new Fine;
        $this->discount = new Discount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getPaymentType() {
        return $this->paymentType;
    }

    public function setPaymentType($paymentType) {
        $this->paymentType = $paymentType;
    }

    public function setInstallmentNumber($installmentNumber) {
        $this->installmentNumber = $installmentNumber;
    }

    public function getInstallmentNumber() {
        return $this->installmentNumber;
    }

    public function setInstructions($instructions) {
        $this->instructions = $instructions;
    }

    public function getInstructions() {
        return $this->instructions;
    }
}