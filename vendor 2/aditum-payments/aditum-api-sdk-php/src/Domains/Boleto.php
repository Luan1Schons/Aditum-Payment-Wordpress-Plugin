<?php

namespace AditumPayments\ApiSDK\Domains;

class Boleto extends Charge {
    public const CHARGE_TYPE = "Boleto";

    private $deadline = NULL;

    public function __construct() {
        $this->customer = new Customer;
        $this->transactions = new Transactions;
    }

    public function setDeadline($deadline) {
        if (strpos($deadline, '/') !== false)
            $this->dealine = $deadline;
        else {
            $this->deadline = new \DateTime('NOW');
            $this->deadline->modify("+{$deadline} day");
            $this->deadline = $this->deadline->format('Y-m-d');
        }
    }

    public function getDeadline() {
        return $this->deadline;
    }

    public function toString() {
        return array(
            "charge" => array(
                "customer" => array(
                    "name" => $this->customer->getName(),
                    "email" => $this->customer->getEmail(),
                    "documentType" => $this->customer->getDocumentType(),
                    "document" =>  $this->customer->getDocument(),
                    "address" => array(
                        "street" => $this->customer->address->getStreet(),
                        "number" => $this->customer->address->getNumber(),
                        "neighborhood" => $this->customer->address->getNeighborhood(),
                        "city" => $this->customer->address->getCity(),
                        "state" => $this->customer->address->getState(),
                        "country" => $this->customer->address->getCountry(),
                        "zipcode" => $this->customer->address->getZipcode(),
                        "complement" => $this->customer->address->getComplement()
                    ),
                    "phone" => array(
                        "countryCode"=> $this->customer->phone->getCountryCode(),
                        "areaCode" => $this->customer->phone->getAreaCode(),
                        "number" => $this->customer->phone->getNumber(),
                        "type" => $this->customer->phone->getType()
                    )
                ),
                "transactions" => [
                    array(
                        "amount" => $this->transactions->getAmount(),
                        "instructions" => $this->transactions->getInstructions(),
                        "fine" => $this->transactions->fine->toString(),
                        "discount" => $this->transactions->discount->toString()
                    ),
                ],
                "source" => 1,
                "deadline" => $this->getDeadline(),
                "sessionId" => $this->getSessionId(),
                "merchantChargeId" => self::getMerchantChargeId()
            ),
        );
    }

    public function toJson() {
        return json_encode($this->toString());
    }
}
