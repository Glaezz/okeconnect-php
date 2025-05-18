<?php

namespace Glaezz\Okeconnect;

/**
 * Okeconnect Configuration
 */
class Config
{

    /**
     * Your merchant's Id
     * 
     * @static
     */
    public static $merchantId;
    /**
     * Your merchant pin
     * 
     * @static
     */
    public static $merchantPin;
    /**
     * Your account password
     * 
     * @static
     */
    public static $accountPassword;
    /**
     * Enable request params sanitizer (validate and modify charge request params).
     * 
     * @static
     */
    public static $isSanitized = false;
    /**
     * Default options for every request
     * 
     * @static
     */
    public static $curlOptions = array();

    /**
     * Okeconnect API URL
     * 
     * @static
     */
    public static $serverUrl;

    const TRANSACTION_BASE_URL = 'https://h2h.okeconnect.com';

    /**
     * Get baseUrl
     * 
     * @return string Okeconnect API URL
     */
    public static function getBaseUrl()
    {
        return Config::$serverUrl . "/trx";
        // return Config::$isProduction ?
        // Config::PRODUCTION_BASE_URL : Config::SANDBOX_BASE_URL;
    }

    public static function load(array $config)
    {
        self::$merchantId = $config['merchantId'] ?? null;
        self::$merchantPin = $config['merchantPin'] ?? null;
        self::$accountPassword = $config['accountPassword'] ?? null;
        self::$serverUrl = $config['serverUrl'] ?? null;
    }
}
