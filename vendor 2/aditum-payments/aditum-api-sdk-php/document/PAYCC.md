## Autorização
Responsável por criar uma nova cobrança, contendo uma ou várias transações autorizadas, usando um ou vários adquirentes. A carga não precisa ser capturada posteriormente.

```php
AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$gateway = new AditumPayments\ApiSDK\Gateway;
$authorization = new AditumPayments\ApiSDK\Domains\Authorization;

// Customer
$authorization->customer->setName("ceres");
$authorization->customer->setEmail("ceres@aditum.co");

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
$authorization->transactions->setInstallmentNumber(2); // Só pode ser maior que 1 se o tipo de transação for crédito.
$authorization->transactions->getAcquirer(AditumPayments\ApiSDK\Enum\AcquirerCode::SIMULADOR); // Valor padrão AditumPayments\ApiSDK\AcquirerCode::ADITUM_ECOM

// Transactions->card
$authorization->transactions->card->setCardNumber("5463373320417272");
$authorization->transactions->card->setCVV("879");
$authorization->transactions->card->setCardholderName("CERES ROHANA");
$authorization->transactions->card->setExpirationMonth(10);
$authorization->transactions->card->setExpirationYear(2022);

$res = $gateway->charge($authorization);

echo "\n\nResposta:\n";
print_r(json_encode($res));

if (isset($res["status"])) {
    if ($res["status"] == AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED) 
        echo "\n\nAprovado!\n";
} else {
    if ($res != NULL)
        echo "httStatus: ".$res["httpStatus"]
        ."\n httpMsg: ".$res["httpMsg"]
        ."\n";
}
```
