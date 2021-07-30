<?php

namespace AditumPayments\ApiSDK\Controller;

use AditumPayments\ApiSDK\Configuration;
use AditumPayments\ApiSDK\Helper\Utils;

class Authorization {

    public function charge($data) {
        $brandName = Utils::getBrandCardBin($data->transactions->card->getCardNumber());
        if ($brandName["status"] == false) {
            Utils::log("\nAuthorization::toJson = Falha ao buscar nome da bandeira do cartão\n");
            $arrayError = array("httpStatus" => $brandName["httpStatus"], "httpMsg" => $brandName["httpMsg"]);
            return $arrayError;
        } else {
            $data->transactions->card->setBrandName($brandName["brand"]);
        }
        
        Utils::log("\n\n => Authorization::charge = Iniciando...\n");
        Utils::log("Authorization::charge = URL ".Configuration::getURL()."\n");

        $ch = curl_init();

        $url = Configuration::getUrl()."charge/authorization";

        Utils::log("Authorization::charge = Url de requisição {$url}\n");
        Utils::log("Authorization::charge = Body da requisição:\n");
        Utils::log($data->toJson());

        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer ".Configuration::getToken(),
                "Content-Length: ".strlen($data->toJson()) 
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => $data->toJson()
        ]);

        $response = curl_exec($ch);
        $errMsg = curl_error($ch);
        $errCode = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($errMsg || $errCode || empty($response) ||  (($httpCode != 200) && ($httpCode != 201))) {
            curl_close($ch);
            $arrayError = array(
                "httpStatus" => $httpCode, 
                "httpMsg" => $response, 
                "code" => $errCode, 
                "msg" => $errMsg);
            
            return $arrayError;
        }

        curl_close($ch);

        $responseJson = json_decode($response);

        if ($responseJson->success != true) {
            $arrayError = array("httpStatus" => '-1', "httpMsg" => $responseJson->errors);
            return $arrayError;
        }

        return array("status" => $responseJson->charge->chargeStatus, "charge" => $responseJson->charge);
    }
}