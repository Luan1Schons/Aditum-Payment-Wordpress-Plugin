<?php

namespace AditumPayments\ApiSDK\Domains;

class Fine {
    private $startDate = NULL;
    private $amount = NULL;
    private $interest = NULL;

    public function setStartDate($startDate) {
        if (strpos($startDate, '/') !== false)
            $this->startDate = $startDate;
        else {
            $this->startDate = new \DateTime('NOW');
            $this->startDate->modify("+{$startDate} day");
            $this->startDate = $this->startDate->format('Y-m-d');
        }
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setInterest($interest) {
        $this->interest = $interest;
    }

    public function getInterest() {
        return $this->interest;
    }

    public function toString() {
        if ($this->getStartDate() == NULL) return "";
        if ($this->getAmount() == NULL) return "";
        if ($this->getInterest() == NULL) return "";

        return array(
            "startDate" => $this->getStartDate(),
            "amount" => $this->getAmount(),
            "interest" => $this->getInterest()
        );
    }

    public function toJson() {
        return json_encode($this->toString());
    }
}