<?php

namespace AditumPayments\ApiSDK\Helper;

use AditumPayments\ApiSDK\Configuration;

abstract class Utils {
    public static function getBrandCardBin($cardNumber) {
        self::log("\n\n => Utils::getBrandCardBin = Iniciando...\n");

        $bin = substr($cardNumber, 0, 4);

        // @TODO: necessário remover quando estiver no novo endpoint        
        $url = Configuration::getURL() == Configuration::PROD_URL? "https://portal-api.aditum.com.br/v1/": "https://portal-dev.aditum.com.br/v1/"; 
        $urlRequest = "{$url}card/bin/brand/{$bin}";

        self::log("Utils::getBrandCardBin = Url de requisição {$urlRequest}\n");

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $urlRequest,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer ".Configuration::getToken(),
                "Content-Length: 0"
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        self::log("Utils::getBrandCardBin = Buscando nome da bandeira\n");

        $response = curl_exec($ch);
        $errMsg = curl_error($ch);
        $errCode = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($errMsg || $errCode || empty($response) ||  (($httpCode != 200) && ($httpCode != 201))) {
            curl_close($ch);

            self::log("Utils::getBrandCardBin = Falha ao buscar nome da bandeira do cartão, httpCode {$httpCode}\n");
            $arrayError = array(
                "status" => false,
                "httpStatus" => $httpCode, 
                "httpMsg" => $response, 
                "code" => $errCode, 
                "msg" => $errMsg);
            
            return $arrayError;
        }

        curl_close($ch);

        $responseJson = json_decode($response);

        if ($responseJson->success != true) {
            self::log("Utils::getBrandCardBin = Falha ao buscar nome da bandeira do cartão, response {$response}\n");
            $arrayError = array("status" => false, "httpStatus" => '-1', "httpMsg" => $responseJson->errors);
            return $arrayError;
        }

        self::log("Utils::getBrandCardBin = Sucesso ao buscar bandeira do cartão {$responseJson->cardBrand}\n");

        return array("status" => true, "brand" => $responseJson->cardBrand);
    }

    public static function log($data) {
        if (Configuration::getLog())
            print_r($data);
    }
}