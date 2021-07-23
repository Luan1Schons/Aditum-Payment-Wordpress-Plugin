<?php
namespace AditumPayments\ApiSDK\Domains;

abstract class Charge {
    public const CHARGE_TYPE = "Undefined";

    public $customer = NULL;
    public $transactions = NULL;

    private $merchantChargeId = "";
    private $sessionId = "";

    public function setMerchantChargeId($merchantChargeId) {
        $this->merchantChargeId = $merchantChargeId;
    }

    public function getMerchantChargeId() {
        return $this->merchantChargeId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    abstract public function toString();
    abstract public function toJson();
}