<?php

namespace Glaezz\Okeconenct;

use Exception;
use Glaezz\Okeconenct\ApiRequestor;
use Glaezz\Okeconenct\Config;
use Glaezz\Okeconenct\Helper;

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
            'pin' => Config::$accountPin,
            'password' => Config::$accountPassword,
        );

        $response = ApiRequestor::get(
            Config::getBaseUrl() . '/balance',
            Config::$merchantId,
            $payloads
        );

        $balanceString = preg_replace('/^Saldo\s+/', '', $response);
        $balanceString = preg_replace('/[^0-9]/', '', $balanceString);

        return (object)[
            'balance' => $balanceString,
        ];
    }

    /**
     * Create Okeconnect transaction.
     *
     * @param string productCode - code for the product to be purchased on Okeconnect. (example: "T5", "S20", "SM20")
     * @param int destination - destination number for the product to be purchased on Okeconnect. (example: 088123456789)
     * @param string refId - unique reference ID for the transaction (from your system). (example: 98710 or "trx-1")
     * @param bool isOpenDenom - whether the produt is open denomination or not (default: false). (example: false for non-open denomination, true for open denomination)
     * @param int amount - amount for the product to be purchased with open denomination. (minimum 10000)
     * @return mixed
     * @throws Exception
     */
    public static function Transaction($productCode, $destination, $refId, $isOpenDenom = false, $amount = 0)
    {
        if ($isOpenDenom) {
            if (!preg_match('/^BBS/', $productCode)) {
                throw new Exception('Check your product code for open denomination. It should start with BBS');
            }

            if ($amount < 10000) {
                throw new Exception('Minimum amount for open denomination is 10.000');
            }


            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$accountPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                'dest' => $destination,
                'refID' => $refId,
                'qty' => $amount,
            );
        } else {
            if (preg_match('/^BBS/', $productCode)) {
                throw new Exception('Check your product code for non-open denomination. It should not start with BBS');
            }
            $payloads = array(
                'memberID' => Config::$merchantId,
                'pin' => Config::$accountPin,
                'password' => Config::$accountPassword,
                'product' => $productCode,
                'dest' => $destination,
                'refID' => $refId,
            );
        }

        $response = ApiRequestor::get(
            Config::getBaseUrl(),
            Config::$merchantId,
            $payloads
        );

        if (preg_match('/GAGAL\.\s*(.*?)\.\s*/', $response, $matches)) {
            // Jika status GAGAL 
            if (preg_match('/Nomor\./', $response)) {
                throw new \Exception("Wrong or incorrect destination number");
            }
            throw new \Exception("failed to create transaction:" . $matches[1]);
            // throw new \Exception($response);
        } elseif (preg_match('/akan diproses\./', $response)) {
            // Jika status PROSES
            $result = Helper::parseTransaction($response);
        }

        return $result;
    }

    public static function getTransactionStatus($transactionId)
    {
        $payloads = array(
            'memberID' => Config::$merchantId,
            'pin' => Config::$accountPin,
            'password' => Config::$accountPassword,
            'trxid' => $transactionId,
        );

        $response = ApiRequestor::get(
            Config::getBaseUrl() . '/status',
            Config::$merchantId,
            $payloads
        );

        if (preg_match('/GAGAL\.\s*(.*?)\.\s*/', $response, $matches)) {
            // Jika status GAGAL 
            if (preg_match('/Nomor\./', $response)) {
                throw new \Exception("Wrong or incorrect destination number");
            }
            throw new \Exception("failed to create transaction:" . $matches[1]);
            // throw new \Exception($response);
        } elseif (preg_match('/akan diproses\./', $response)) {
            // Jika status PROSES
            $result = Helper::parseTransaction($response);
        }

        return $result;
    }

    public static function CallbackHandler($callbackData)
    {
        $result = Helper::parseTransaction($callbackData);

        return $result;
    }
}
