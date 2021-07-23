<?php

namespace AditumPayments\ApiSDK;

use AditumPayments\ApiSDK\Domains\Boleto;
use AditumPayments\ApiSDK\Controller\Login;

abstract class Configuration {
    private static $url = NULL;

    private static $cnpj = "";
    private static $merchantToken = "";

    private static $token = "";

    private static $log = false;

    public const PROD_URL = "https://payment.aditum.com.br/v2/";
    public const DEV_URL  = "https://payment-dev.aditum.com.br/v2/";

    final public static function initialize() {
        self::$url = self::PROD_URL;
        self::$log = false;
    }

    final public static function login() {
        $login = new Login;
        $data = $login->requestToken(self::$cnpj, self::$merchantToken, self::$url);

        if (isset($data["token"])) self::$token = $data["token"];

        return $data;
    }

    final public static function setUrl($url) {
        self::$url = $url;
    }

    final public static function getUrl() {
        return self::$url;
    }

    final public static function setCnpj($cnpj) {
        self::$cnpj = $cnpj;
    }

    final public static function getCnpj() {
        return self::$cnpj;
    }

    final public static function setMerchantToken($merchantToken) {
        self::$merchantToken = $merchantToken;
    }

    final public static function getMerchantToken() {
        return self::$merchantToken;
    }

    final public static function setToken($token) {
        self::$token = $token;
    }

    final public static function getToken() {
        return self::$token;
    }

    final public static function setLog($status) {
        self::$log = $status;
    }

    final public static function getLog() {
        return self::$log;
    }
}
