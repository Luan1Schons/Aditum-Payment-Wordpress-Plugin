<?php

namespace AditumPayments\ApiSDK\Tests;

use AditumPayments\ApiSDK\Configuration;
use AditumPayments\ApiSDK\Gateway;
use AditumPayments\ApiSDK\Domains\Authorization;
use AditumPayments\ApiSDK\Enum\DocumentType;
use AditumPayments\ApiSDK\Enum\PhoneType;
use AditumPayments\ApiSDK\Enum\PaymentType;
use AditumPayments\ApiSDK\Enum\ChargeStatus;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{

    public function testeAuthorizationAproved()
    {
        Configuration::initialize();
        Configuration::setUrl(Configuration::DEV_URL);
        Configuration::setCnpj("83032272000109");
        Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
        Configuration::login();

        $gateway = new Gateway;
        $authorization = new Authorization;
        
        $authorization->setMerchantChargeId("");
        $authorization->setSessionId("");
        
        // Customer
        $authorization->customer->setName("ceres");
        $authorization->customer->setEmail("ceres@aditum.co");
        $authorization->customer->setDocumentType(DocumentType::CPF);
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
        $authorization->customer->phone->setType(PhoneType::MOBILE);
        
        // Transactions
        $authorization->transactions->setAmount(100);
        $authorization->transactions->setPaymentType(PaymentType::CREDIT);
        $authorization->transactions->setInstallmentNumber(2); // Só pode ser maior que 1 se o tipo de transação for crédito.
        
        // Transactions->card
        $authorization->transactions->card->setCardNumber("4444333322221111"); // Aprovado
        
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
        
        if (isset($res["status"])) {
            $this->assertEquals($res["status"], ChargeStatus::AUTHORIZED);
        } 
    }

    public function testeAuthorizationPreAuthorizad()
    {
        Configuration::initialize();
        Configuration::setUrl(Configuration::DEV_URL);
        Configuration::setCnpj("83032272000109");
        Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
        Configuration::login();

        $gateway = new Gateway;
        $authorization = new Authorization;
        
        $authorization->setMerchantChargeId("");
        $authorization->setSessionId("");
        
        // Customer
        $authorization->customer->setName("ceres");
        $authorization->customer->setEmail("ceres@aditum.co");
        $authorization->customer->setDocumentType(DocumentType::CPF);
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
        $authorization->customer->phone->setType(PhoneType::MOBILE);
        
        // Transactions
        $authorization->transactions->setAmount(100);
        $authorization->transactions->setPaymentType(PaymentType::CREDIT);
        $authorization->transactions->setInstallmentNumber(2); // Só pode ser maior que 1 se o tipo de transação for crédito.
        
        // Transactions->card
        $authorization->transactions->card->setCardNumber("4222222222222224"); // Aprovado
        
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
        
        if (isset($res["status"])) {
            $this->assertEquals($res["status"], ChargeStatus::PRE_AUTHORIZED);
        } 
    }

    public function testeAuthorizationNotAuthorizad()
    {
        Configuration::initialize();
        Configuration::setUrl(Configuration::DEV_URL);
        Configuration::setCnpj("83032272000109");
        Configuration::setMerchantToken("mk_P1kT7Rngif1Xuylw0z96k3");
        Configuration::login();

        $gateway = new Gateway;
        $authorization = new Authorization;
        
        $authorization->setMerchantChargeId("");
        $authorization->setSessionId("");
        
        // Customer
        $authorization->customer->setName("ceres");
        $authorization->customer->setEmail("ceres@aditum.co");
        $authorization->customer->setDocumentType(DocumentType::CPF);
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
        $authorization->customer->phone->setType(PhoneType::MOBILE);
        
        // Transactions
        $authorization->transactions->setAmount(100);
        $authorization->transactions->setPaymentType(PaymentType::CREDIT);
        $authorization->transactions->setInstallmentNumber(2); // Só pode ser maior que 1 se o tipo de transação for crédito.
        
        // Transactions->card
        $authorization->transactions->card->setCardNumber("4444333322221112"); // Aprovado
        
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
        
        if (isset($res["status"])) {
            $this->assertEquals($res["status"], ChargeStatus::NOT_AUTHORIZED);
        } 
    }
}
