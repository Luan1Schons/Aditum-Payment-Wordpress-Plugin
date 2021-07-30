<?php

namespace AditumPayments\ApiSDK\Tests;

use AditumPayments\ApiSDK\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testeInitialize()
    {
        Configuration::initialize();

        $this->assertEquals(Configuration::getUrl(), Configuration::PROD_URL);
        $this->assertFalse(Configuration::getLog());
    }

    public function testeLogin()
    {
        Configuration::initialize();

        Configuration::setUrl(Configuration::DEV_URL);
        Configuration::setCnpj("83032272000109");
        Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
        
        $ret = Configuration::login();
        $this->assertNotEmpty($ret);

        Configuration::initialize();

        Configuration::setUrl(Configuration::DEV_URL);
        Configuration::setCnpj("83032272000108");
        Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
        
        $ret = Configuration::login();
        $this->assertEquals($ret['httpStatus'], 401);
    }

}
