<?php

namespace Glaezz\Okeconnect;

use Exception;
use Glaezz\Okeconnect\ApiRequestor;
use Glaezz\Okeconnect\Config;
use Glaezz\Okeconnect\Helper;
use PhpParser\Node\Stmt\Static_;

class ClientApi
{


    /**
     * Check Okeconnect Balance.
     *
     * @return mixed
     * @throws Exception
     */
    public static function getBalance()
    {
        $payloads = array(
            'memberID' => Config::$merchantId,
            'pin' => Config::$merchantPin,
            'password' => Config::$accountPassword,
        );


        $response = ApiRequestor::get(
            Config::getBaseUrl() . '/balance',
            $payloads
        );



        return Helper::responseParser($response, "balance");
    }

    /**
     * Create Okeconnect transaction.
     *
     * @param string productCode - code for the product to be purchased on Okeconnect. (example: "T5", "S20", "SM20")
     * @param int destination - destination number for the product to be purchased on Okeconnect. (example: 088123456789)
     * @param string referenceId - unique reference ID for the transaction (from your system). (example: 98710 or "trx-1")
     * @param bool isOpenDenom - whether the produt is open denomination or not (default: false). (example: false for non-open denomination, true for open denomination)
     * @param int amount - amount for the product to be purchased with open denomination. (minimum 10000)
     * @return mixed
     * @throws Exception
     */
    public static function createTransaction($productCode, $destination, $referenceId, $amount = 0)
    {
        if (preg_match('/^BBS/', $productCode)) {

            $arr_validate = array(
                'product_code' => $productCode,
                'destination' => $destination,
                'reference_id' => $referenceId,
                'amount' => $amount,
            );

            $validator = Helper::validator($arr_validate);
            if ($validator["status_code"] != 200) {
                return $validator;
            }

            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$merchantPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                'dest' => $destination,
                'refID' => $referenceId,
                'qty' => $amount,
            );
        } else {

            $arr_validate = array(
                'product_code' => $productCode,
                'destination' => $destination,
                'reference_id' => $referenceId,
            );

            $validator = Helper::validator($arr_validate);
            if ($validator["status_code"] != 200) {
                return $validator;
            }

            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$merchantPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                'dest' => $destination,
                'refID' => $referenceId,
            );
        }

        $response = ApiRequestor::get(
            Config::getBaseUrl(),
            $payloads
        );

        return Helper::responseParser($response, "create");

        // if (preg_match('/GAGAL\.\s*(.*?)\.\s*/', $response, $matches)) {
        //     // Jika status GAGAL 
        //     if (preg_match('/Nomor\./', $response)) {
        //         throw new \Exception("Wrong or incorrect destination number");
        //     }
        //     throw new \Exception("failed to create transaction:" . $matches[1]);
        //     // throw new \Exception($response);
        // } elseif (preg_match('/akan diproses\./', $response)) {
        //     // Jika status PROSES
        //     $result = Helper::responseParser($response);
        // }
    }

    /**
     * Get transaction status. (Conditional use, dont use for your history transaction. save every transaction to your database!)
     *
     * @param string productCode - code for the product. (example: "T5", "S20", "SM20")
     * @param int destination - destination number of the product. (example: 088123456789)
     * @param int referenceId - unique reference ID of the transaction (from your system). (example: 98710 or "trx-1")
     * @param int amount - quantity of the product. ONLY FOR OPEN DENOM TRANSACTION (minimum 10000)
     * @return mixed
     * @throws Exception
     */
    public static function getTransactionStatus($productCode, $destination, $referenceId, $amount = 0)
    {
        
        if (preg_match('/^BBS/', $productCode)) {
            $arr_validate = array(
                'product_code' => $productCode,
                'destination' => $destination,
                'reference_id' => $referenceId,
                'amount' => $amount,
            );
            
            $validator = Helper::validator($arr_validate);
            if ($validator["status_code"] != 200) {
                return $validator;
            }
            
            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$merchantPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                "dest" => $destination,
                "qty" => $amount,
                'refID' => $referenceId,
                'check' => 1
            );
        } else {
            $arr_validate = array(
                'product_code' => $productCode,
                'destination' => $destination,
                'reference_id' => $referenceId,
            );

            $validator = Helper::validator($arr_validate);
            if ($validator["status_code"] != 200) {
                return $validator;
            }

            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$merchantPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                "dest" => $destination,
                'refID' => $referenceId,
                'check' => 1
            );
        }

        $response = ApiRequestor::get(
            Config::getBaseUrl(),
            $payloads
        );


        return Helper::responseParser($response, "check");

        // if (preg_match('/GAGAL\.\s*(.*?)\.\s*/', $response, $matches)) {
        //     // Jika status GAGAL 
        //     if (preg_match('/Nomor\./', $response)) {
        //         throw new \Exception("Wrong or incorrect destination number");
        //     }
        //     throw new \Exception("failed to create transaction:" . $matches[1]);
        //     // throw new \Exception($response);
        // } elseif (preg_match('/akan diproses\./', $response)) {
        //     // Jika status PROSES
        //     $result = Helper::responseParser($response);
        // }
    }

    /**
     * Callback handler for Okeconnect transaction.
     *
     * @param string $callbackData - message parameter from Okeconnect callback.
     * @return mixed
     * @throws Exception
     */
    public static function callbackHandler($callbackData)
    {   
        
        $callback = Helper::responseParser($callbackData, "callback");

        if (preg_match('/^BBS/', $callback['product']['code'])) {
            return [
                "status_code" => 500,
                "message" => "Callback not compatible with open denomination product",
                "data" => $callback,
            ];
        }

        $check = self::getTransactionStatus($callback['product']['code'], $callback['destination'], $callback['reference_id'], $callback['product']['amount']);


        if ($check['status_code'] != 200) {
            return $check;
        }

        if ($check['reference_id'] == $callback['reference_id']) {
            if ($check['transaction_status'] == $callback['transaction_status']) {
                return $callback;
            } else {
                return [
                    "status_code" => 400,
                    "message" => "Transaction status not match",
                ];
            }
        } else {
            return [
                "status_code" => 400,
                "message" => "Reference ID not match",
            ];
        }
    }
}
