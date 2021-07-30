# Authentication
A autenticação é a forma que deve ser utilizada para gerar um Token de acesso, para que você consiga utilizar os endpoints.

```php
AditumPayments\ApiSDK\Configuration::initialize();

AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL); // Caso não defina a url, será usada de produção
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);

$res = AditumPayments\ApiSDK\Configuration::login();

if (isset($res["token"])) {
	echo  $res["token"]."\n";
	
} else {
	echo  "httStatus: ".$res["httpStatus"]
		."\n httpMsg: ".$res["httpMsg"]
		."\n";
}
```
