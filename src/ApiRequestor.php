<?php

namespace Glaezz\Okeconnect;

use Exception;

/**
 * Send request to Okeconnect API
 * Better don't use this class directly, please use ClientApi instead
 */

class ApiRequestor
{

    /**
     * Send GET request
     *
     * @param string $url
     * @param string $merchantId
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function get($url, $data_hash)
    {
        return self::remoteCall($url, $data_hash, 'GET');
    }

    /**
     * Send POST request
     *
     * @param string $url
     * @param string $merchantId
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function post($url, $data_hash)
    {
        return self::remoteCall($url, $data_hash, 'POST');
    }

    /**
     * Send PATCH request
     *
     * @param string $url
     * @param string $merchantId
     * @param mixed[] $data_hash
     * @return mixed
     * @throws Exception
     */
    public static function patch($url, $data_hash)
    {
        return self::remoteCall($url, $data_hash, 'PATCH');
    }

    /**
     * Actually send request to API server
     *
     * @param string $url
     * @param mixed[] $data_hash
     * @param bool $post
     * @return mixed
     * @throws Exception
     */
    public static function remoteCall($url, $data_hash, $method)
    {
        $ch = curl_init();

        // if (!$merchantId) {
        //     throw new Exception(
        //         'The MerchantId is null, You need to set the MerchantId from Config. Please double-check Config and MerchantId. ' .
        //             'You can check from the Okeconnect Dashboard. ' .
        //             'See https://okeconnect.com/integrasi/trx_ip ' .
        //             'for the details or contact support at https://t.me/orderkuota if you have any questions.'
        //     );
        // } else {
        //     if ($merchantId == "") {
        //         throw new Exception(
        //             'The MerchantId is invalid, as it is an empty string. Please double-check your MerchantId. ' .
        //                 'You can check from the Okeconnect Dashboard. ' .
        //                 'See https://okeconnect.com/integrasi/trx_ip ' .
        //                 'for the details or contact support at https://t.me/orderkuota if you have any questions.'
        //         );
        //     } elseif (preg_match('/\s/', $merchantId)) {
        //         throw new Exception(
        //             'The MerchantId is contains white-space. Please double-check your MerchantId. ' .
        //                 'You can check from the Okeconnect Dashboard. ' .
        //                 'See https://okeconnect.com/integrasi/trx_ip ' .
        //                 'for the details or contact support at https://t.me/orderkuota if you have any questions.'
        //         );
        //     }
        // }


        $curl_options = array(
            CURLOPT_URL => $url,
            // CURLOPT_HTTPHEADER => array(
            //     // 'Content-Type: application/json',
            //     // 'Accept: application/json',
            //     'User-Agent: okeconnect-php-v1.0.0',
            // ),
            // CURLOPT_RETURNTRANSFER => 1
            CURLOPT_TIMEOUT => 60,
        );


        // merging with Config::$curlOptions
        if (count(Config::$curlOptions)) {
            // We need to combine headers manually, because it's array and it will no be merged
            if (Config::$curlOptions[CURLOPT_HTTPHEADER]) {
                $mergedHeaders = array_merge($curl_options[CURLOPT_HTTPHEADER], Config::$curlOptions[CURLOPT_HTTPHEADER]);
                $headerOptions = array(CURLOPT_HTTPHEADER => $mergedHeaders);
            } else {
                $mergedHeaders = array();
                $headerOptions = array(CURLOPT_HTTPHEADER => $mergedHeaders);
            }

            $curl_options = array_replace_recursive($curl_options, Config::$curlOptions, $headerOptions);
        }

        if ($method == 'GET') {
            if (!empty($data_hash)) {
                $query = http_build_query($data_hash);
                $curl_options[CURLOPT_URL] = $url . '?' . $query;
            } else {
                $curl_options[CURLOPT_URL] = $url;
            }
        }

        // dd($curl_options);

        curl_setopt_array($ch, $curl_options);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);



        if ($result === false) {
            throw new Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));
        } else {
            // try {
            //     $result_array = json_decode($result);
            // } catch (Exception $e) {
            //     throw new Exception("API Request Error unable to json_decode API response: " . $result . ' | Request url: ' . $url);
            // }
            // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // if (isset($result_array->status_code) && $result_array->status_code >= 401 && $result_array->status_code != 407) {
            //     throw new Exception('Okeconnect API is returning API error. HTTP status code: ' . $result_array->status_code . ' API response: ' . $result, $result_array->status_code);
            // } elseif ($httpCode >= 400) {
            //     throw new Exception('Okeconnect API is returning API error. HTTP status code: ' . $httpCode . ' API response: ' . $result, $httpCode);
            // } else {
            //     return $result_array;
            // }
            return $result;
        }
    }
}
