<?php

require  '../vendor/autoload.php';

AditumPayments\ApiSDK\Configuration::initialize();
AditumPayments\ApiSDK\Configuration::setUrl(AditumPayments\ApiSDK\Configuration::DEV_URL);
AditumPayments\ApiSDK\Configuration::setCnpj("83032272000109");
AditumPayments\ApiSDK\Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
AditumPayments\ApiSDK\Configuration::setlog(true);
AditumPayments\ApiSDK\Configuration::login();

$brandName = AditumPayments\ApiSDK\Helper\Utils::getBrandCardBin("4444333322221111");
if ($brandName["status"] == false) {
    echo "Falha ao buscar nome da bandeira do cartão\n";
    echo "\nhttStatus: ".$brandName["httpStatus"]
        ."\nhttpMsg: ".$brandName["httpMsg"]
        ."\n";
} else {
    echo "\n".$brandName["brand"]."\n";
}

