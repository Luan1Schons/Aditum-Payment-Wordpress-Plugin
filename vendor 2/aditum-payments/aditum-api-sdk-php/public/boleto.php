<?php

require  '../vendor/autoload.php';

AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$gateway = new AditumPayments\ApiSDK\Gateway;
$boleto = new AditumPayments\ApiSDK\Domains\Boleto;

$boleto->setMerchantChargeId("");
$boleto->setSessionId("");

$boleto->setDeadline("2");

// Customer
$boleto->customer->setId("00002");
$boleto->customer->setName("fulano");
$boleto->customer->setEmail("fulano@aditum.co");
$boleto->customer->setDocumentType(AditumPayments\ApiSDK\Enum\DocumentType::CPF);
$boleto->customer->setDocument("14533859755");

// Customer->address
$boleto->customer->address->setStreet("Avenida Salvador");
$boleto->customer->address->setNumber("5401");
$boleto->customer->address->setNeighborhood("Recreio dos bandeirantes");
$boleto->customer->address->setCity("Rio de janeiro");
$boleto->customer->address->setState("RJ");
$boleto->customer->address->setCountry("BR");
$boleto->customer->address->setZipcode("2279714");
$boleto->customer->address->setComplement("");

// Customer->phone
$boleto->customer->phone->setCountryCode("55");
$boleto->customer->phone->setAreaCode("21");
$boleto->customer->phone->setNumber("98491715");
$boleto->customer->phone->setType(AditumPayments\ApiSDK\Enum\PhoneType::MOBILE);

// Transactions
$boleto->transactions->setAmount(30000);
$boleto->transactions->setInstructions("Crédito de teste");

// Transactions->fine (opcional)
$boleto->transactions->fine->setStartDate("2");
$boleto->transactions->fine->setAmount(300);
$boleto->transactions->fine->setInterest(10);

// Transactions->discount (opcional)
// $boleto->transactions->discount->setType(AditumPayments\ApiSDK\Enum\DiscountType::FIXED);
// $boleto->transactions->discount->setAmount(200);
// $boleto->transactions->discount->setDeadline("1");

$res = $gateway->charge($boleto);

echo "\n\nResposta:\n";
print_r(json_encode($res));

if (isset($res["status"])) {
    if ($res["status"] == AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED) 
        echo "\n\nPRÉ AUTORIZADO!\n";
} else {
    if ($res != NULL)
        echo "\nhttStatus: ".$res["httpStatus"]
            ."\nhttpMsg: ".$res["httpMsg"]
            ."\n";
}