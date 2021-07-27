<?php

namespace AditumPayments\ApiSDK\Domains;

class Authorization extends Charge
{
    public const CHARGE_TYPE = "Authorization";

    public function __construct()
    {
        $this->customer = new Customer;
        $this->transactions = new Transactions;
    }

    public function toString()
    {
        return array("charge" => array(
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
                        "card" => array(
                            "cardNumber" => $this->transactions->card->getCardNumber(),
                            "cvv" => $this->transactions->card->getCVV(),
                            "cardholderName" => $this->transactions->card->getCardholderName(),
                            "expirationMonth" => $this->transactions->card->getExpirationMonth(),
                            "expirationYear" => $this->transactions->card->getExpirationYear(),
                            "brandName" => $this->transactions->card->getBrandName(),
                            "cardholderDocument" => $this->customer->getDocument(),
                            "billingAddress" => array(
                                "street" => $this->customer->address->getStreet(),
                                "number" => $this->customer->address->getNumber(),
                                "neighborhood" => $this->customer->address->getNeighborhood(),
                                "city" => $this->customer->address->getCity(),
                                "state" => $this->customer->address->getState(),
                                "country" => $this->customer->address->getCountry(),
                                "zipcode" => $this->customer->address->getZipcode(),
                                "complement" => $this->customer->address->getComplement()
                            )
                        ),
                        "installmentNumber" => $this->transactions->getInstallmentNumber(),
                        "paymentType" => $this->transactions->getPaymentType(),
                        "installmentType" => "Issuer",
                        "amount" => $this->transactions->getAmount(),
                        "acquirer" => "Simulator"
                    ),
                ],
                "DataContract" => 0,
                "source" => 1,
                "capture" => false,
                "sessionId" => self::getSessionId(),
                "merchantChargeId" => self::getMerchantChargeId()
            ));
    }

    public function toJson()
    {
        return json_encode($this->toString());
    }
}
