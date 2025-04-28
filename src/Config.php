<?php

namespace Glaezz\Okeconenct;

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
     * Your account pin
     * 
     * @static
     */
    public static $accountPin;
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

    const TRANSACTION_BASE_URL = 'https://h2h.okeconnect.com/trx';

    /**
     * Get baseUrl
     * 
     * @return string Okeconnect API URL
     */
    public static function getBaseUrl()
    {
        Config::TRANSACTION_BASE_URL;
        // return Config::$isProduction ?
        // Config::PRODUCTION_BASE_URL : Config::SANDBOX_BASE_URL;
    }
}
