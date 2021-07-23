<?php

namespace AditumPayments\ApiSDK;

use AditumPayments\ApiSDK\Configuration;
use AditumPayments\ApiSDK\Controller\Authorization;
use AditumPayments\ApiSDK\Controller\Boleto;
use AditumPayments\ApiSDK\Controller\PreAuthorization;
use AditumPayments\ApiSDK\Helper\Utils;

class Gateway {

    public function charge($data) {
        switch($data::CHARGE_TYPE) {
            case "Authorization":
                $authorization = new Authorization;
                return $authorization->charge($data);
            case "Boleto":
                $boleto = new Boleto;
                return $boleto->charge($data);
            case "PreAuthorization":
                $preAuthorization = new PreAuthorization;
                return $preAuthorization->charge($data);
            case "Undefined":
                Utils::log("Payment::charge = Defina o tipo de transação\n");
                return NULL;
                break;
            default:
            Utils::log("Payment::charge = Undefined charge type\n");
                return NULL;
        }
    }

    public function billingInformation($id) {
        Utils::log("\n\n => Gateway::billingInformation = Iniciando...\n");

        $ch = curl_init();

        $url = Configuration::getUrl()."charge/{$id}";

        Utils::log("Gateway::billingInformation = Url de requisição {$url}\n");

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer ".Configuration::getToken()
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1
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
            $arrayError = array("code" => '-1', "httpMsg" => $responseJson->errors);
            return $arrayError;
        }

        return array("status" => $responseJson->charge->chargeStatus, "charge" => $responseJson->charge);
    }

    // @TODO: A desenvolver
    public function checkoutByLink() {}

}
