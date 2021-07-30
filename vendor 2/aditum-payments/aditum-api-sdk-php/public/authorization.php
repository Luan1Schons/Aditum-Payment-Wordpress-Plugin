<?php

require  '../vendor/autoload.php';

AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$gateway = new AditumPayments\ApiSDK\Gateway;
$authorization = new AditumPayments\ApiSDK\Domains\Authorization;

$authorization->setMerchantChargeId("");
$authorization->setSessionId("");

// Customer
$authorization->customer->setName("fulano");
$authorization->customer->setEmail("fulano@aditum.co");
$authorization->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CPF);
$authorization->customer->setDocument("14533859755");

// Customer->address
$authorization->customer->address->setStreet("Avenida Salvador");
$authorization->customer->address->setNumber("5401");
$authorization->customer->address->setNeighborhood("Recreio dos bandeirantes");
$authorization->customer->address->setCity("Rio de janeiro");
$authorization->customer->address->setState("RJ");
$authorization->customer->address->setCountry("BR");
$authorization->customer->address->setZipcode("2279714");
$authorization->customer->address->setComplement("");

// Customer->phone
$authorization->customer->phone->setCountryCode("55");
$authorization->customer->phone->setAreaCode("21");
$authorization->customer->phone->setNumber("98491715");
$authorization->customer->phone->setType(AditumPayments\ApiSDK\Enum\PhoneType::MOBILE);

// Transactions
$authorization->transactions->setAmount(100);
$authorization->transactions->setPaymentType(AditumPayments\ApiSDK\Enum\PaymentType::CREDIT);
$authorization->transactions->setInstallmentNumber(1); // Só pode ser maior que 1 se o tipo de transação for crédito.

$authorization->transactions->card->setCardNumber("4444333322221111"); // Aprovado
// $authorization->transactions->card->setCardNumber("4222222222222224"); // Pendente e aprovar posteriormente
// $authorization->transactions->card->setCardNumber("4222222222222225"); // Pendente e negar posteriormente
// $authorization->transactions->card->setCardNumber("4444333322221112"); // Negar

$authorization->transactions->card->setCardholderDocument("14533859755");
$authorization->transactions->card->setCVV("879");
$authorization->transactions->card->setCardholderName("CERES ROHANA");
$authorization->transactions->card->setExpirationMonth(10);
$authorization->transactions->card->setExpirationYear(2022);
$authorization->transactions->card->billingAddress->setStreet("Avenida Salvador");
$authorization->transactions->card->billingAddress->setNumber("5401");
$authorization->transactions->card->billingAddress->setNeighborhood("Recreio dos bandeirantes");
$authorization->transactions->card->billingAddress->setCity("Rio de janeiro");
$authorization->transactions->card->billingAddress->setState("RJ");
$authorization->transactions->card->billingAddress->setCountry("BR");
$authorization->transactions->card->billingAddress->setZipcode("2279714");
$authorization->transactions->card->billingAddress->setComplement("");

$res = $gateway->charge($authorization);

echo "\n\nResposta:\n";
print_r(json_encode($res));

if (isset($res["status"])) {
    if ($res["status"] == AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED) 
        echo "\n\nAprovado!";
} else {
    if ($res != NULL)
        echo "\nhttStatus: ".$res["httpStatus"]
            ."\nhttpMsg: ".$res["httpMsg"]
            ."\n";
}
echo "\n";