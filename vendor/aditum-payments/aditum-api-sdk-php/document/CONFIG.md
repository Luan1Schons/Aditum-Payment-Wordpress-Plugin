# Configuration
Dentro dessa classe podemos definir e obter informações como a url de desenvolvimento e produção, merchanToken e CNPJ.

```php
AditumPayments\ApiSDK\Configuration::initialize();

AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL); // Caso não defina a url, será usada de produção
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);

print_r(AditumPayments\ApiSDK\Configuration::login());
```
