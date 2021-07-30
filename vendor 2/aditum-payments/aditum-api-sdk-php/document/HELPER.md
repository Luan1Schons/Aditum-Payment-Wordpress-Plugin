## Utils
Responsável por criar ter funcções de ajuda para a lib.

*billingInformation*

```php
AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$brandName = AditumPayments\ApiSDK\Helper\Utils::getBrandCardBin("5463373320417272");
if ($brandName == NULL) {
    echo "Authorization::toJson = Falha ao buscar nome da bandeira do cartão\n";
    return NULL;
} else {
    echo "\n\n".$brandName."\n";
}
```
