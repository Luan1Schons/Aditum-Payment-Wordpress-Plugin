<?php

require  '../vendor/autoload.php';

AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$gateway = new AditumPayments\ApiSDK\Gateway;
$preAuthorization = new AditumPayments\ApiSDK\Domains\PreAuthorization;

$preAuthorization->setMerchantChargeId("");
$preAuthorization->setSessionId("");

// Customer
$preAuthorization->customer->setName("fulano");
$preAuthorization->customer->setEmail("fulano@aditum.co");
$preAuthorization->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CPF);
$preAuthorization->customer->setDocument("14533859755");

// Customer->address
$preAuthorization->customer->address->setStreet("Avenida Salvador");
$preAuthorization->customer->address->setNumber("5401");
$preAuthorization->customer->address->setNeighborhood("Recreio dos bandeirantes");
$preAuthorization->customer->address->setCity("Rio de janeiro");
$preAuthorization->customer->address->setState("RJ");
$preAuthorization->customer->address->setCountry("BR");
$preAuthorization->customer->address->setZipcode("2279714");
$preAuthorization->customer->address->setComplement("");

// Customer->phone
$preAuthorization->customer->phone->setCountryCode("55");
$preAuthorization->customer->phone->setAreaCode("21");
$preAuthorization->customer->phone->setNumber("98491715");
$preAuthorization->customer->phone->setType(AditumPayments\ApiSDK\Enum\PhoneType::MOBILE);

// Transactions
$preAuthorization->transactions->setAmount(100);
$preAuthorization->transactions->setPaymentType(AditumPayments\ApiSDK\Enum\PaymentType::CREDIT);
$preAuthorization->transactions->setInstallmentNumber(2); // Só pode ser maior que 1 se o tipo de transação for crédito.

// Transactions->card
$preAuthorization->transactions->card->setCardNumber("4444333322221111"); // Aprovado
// $preAuthorization->transactions->card->setCardNumber("4222222222222224"); // Pendente e aprovar posteriormente
// $preAuthorization->transactions->card->setCardNumber("4222222222222225"); // Pendente e negar posteriormente
// $preAuthorization->transactions->card->setCardNumber("4444333322221112"); // Negar

$authorization->transactions->card->setCardholderDocument("14533859755");
$preAuthorization->transactions->card->setCVV("879");
$preAuthorization->transactions->card->setCardholderName("CERES ROHANA");
$preAuthorization->transactions->card->setExpirationMonth(10);
$preAuthorization->transactions->card->setExpirationYear(2022);
$preAuthorization->transactions->card->billingAddress->setStreet("Avenida Salvador");
$preAuthorization->transactions->card->billingAddress->setNumber("5401");
$preAuthorization->transactions->card->billingAddress->setNeighborhood("Recreio dos bandeirantes");
$preAuthorization->transactions->card->billingAddress->setCity("Rio de janeiro");
$preAuthorization->transactions->card->billingAddress->setState("RJ");
$preAuthorization->transactions->card->billingAddress->setCountry("BR");
$preAuthorization->transactions->card->billingAddress->setZipcode("2279714");
$preAuthorization->transactions->card->billingAddress->setComplement("");

$res = $gateway->charge($preAuthorization);

echo "\n\nResposta:\n";
print_r(json_encode($res));

if (isset($res["status"])) {
    if ($res["status"] == AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED) 
        echo "\n\nPRE_AUTHORIZED!\n";
} else {
    if ($res != NULL)
        echo "\nhttStatus: ".$res["httpStatus"]
            ."\nhttpMsg: ".$res["httpMsg"]
            ."\n";
}
