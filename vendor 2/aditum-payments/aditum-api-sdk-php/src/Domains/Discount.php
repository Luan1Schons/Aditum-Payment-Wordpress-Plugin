<?php

namespace AditumPayments\ApiSDK\Domains;

class Discount {
    private $type = NULL;
    private $amount = NULL;
    private $deadline = NULL;

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setDeadline($deadline) {
        if (strpos($deadline, '/') !== false)
            $this->dealine = $deadline;
        else {
            $this->deadline = new \DateTime('NOW');
            $this->deadline->modify("- {$deadline} day");
            $this->deadline = $this->deadline->format('Y-m-d');
        }
    }

    public function getDeadline() {
        return $this->deadline;
    }


    public function toString() {
        if ($this->getType() == NULL) return "";
        if ($this->getAmount() == NULL) return "";
        if ($this->getDeadline() == NULL) return "";

        return array(
            "type" => $this->getType(),
            "amount" => $this->getAmount(),
            "deadline" => $this->getDeadline()
    );
    }

    public function toJson() {
        return json_encode($this->toString());
    }
}