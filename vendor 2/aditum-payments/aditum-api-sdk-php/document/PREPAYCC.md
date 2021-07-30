## Pré-autorização
Responsável por criar uma nova cobrança, contendo uma ou várias transações pré-autorizadas, usando um ou vários adquirentes. A carga pode ser capturada posteriormente.

```php
AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$gateway = new AditumPayments\ApiSDK\Gateway;
$preAuthorization = new AditumPayments\ApiSDK\Domains\PreAuthorization;


$preAuthorization->setSessionId("");

// Customer
$preAuthorization->customer->setName("ceres");
$preAuthorization->customer->setEmail("ceres@aditum.co");

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
$preAuthorization->transactions->getAcquirer(AditumPayments\ApiSDK\Enum\AcquirerCode::SIMULADOR); // Valor padrão AditumPayments\ApiSDK\AcquirerCode::ADITUM_ECOM

// Transactions->card
$preAuthorization->transactions->card->setCardNumber("5463373320417272");
$preAuthorization->transactions->card->setCVV("879");
$preAuthorization->transactions->card->setCardholderName("CERES ROHANA");
$preAuthorization->transactions->card->setExpirationMonth(10);
$preAuthorization->transactions->card->setExpirationYear(2022);

$res = $gateway->charge($preAuthorization);

echo "\n\nResposta:\n";
print_r(json_encode($res));

if (isset($res["status"])) {
    if ($res["status"] == AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED) 
        echo "\n\nPRE_AUTHORIZED!\n";
} else {
    if ($res != NULL)
        echo "httStatus: ".$res["httpStatus"]
        ."\n httpMsg: ".$res["httpMsg"]
        ."\n";
}

```
