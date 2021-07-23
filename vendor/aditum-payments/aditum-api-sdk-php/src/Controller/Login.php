<?php

namespace AditumPayments\ApiSDK\Controller;

use AditumPayments\ApiSDK\Configuration;
use AditumPayments\ApiSDK\Helper\Utils;

class Login {

    public function requestToken($cnpj, $merchantToken, $url) {
        Utils::log("\n\n => Login::requestToken = Iniciando...\n");
        Utils::log("Login::requestToken = URL ".Configuration::getURL()."\n");

        $merchantCredential = password_hash($cnpj."".$merchantToken, PASSWORD_BCRYPT, [
            'cost' => 12,
        ]);

        $ch = curl_init();

        $url = $url."merchant/auth";

        Utils::log("Login::requestToken = Merchant Credential {$merchantCredential}\n");
        Utils::log("Login::requestToken = Url de requisição {$url}\n");

        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "Authorization: {$merchantCredential}",
                "merchantCredential: {$cnpj}",
                "Content-Length: 0"
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
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

        Utils::log("Login::requestToken = token {$responseJson->accessToken}\n");
        return array("token" => $responseJson->accessToken, "refreshToken" => $responseJson->refreshToken);
    }

    // @TODO: A implementar
    public function requestRefreshToken() {}
}