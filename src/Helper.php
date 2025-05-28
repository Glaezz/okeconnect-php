<?php

namespace Glaezz\Okeconnect;

use Exception;

class Helper
{
    public static function parseCreate($text)
    {
        if (preg_match('/R#([\w\-]+)\s+([A-Z0-9]+)\.(\d+)\s+GAGAL\.\s+(.+)/i', $text, $m)) {
            $result['status_code'] = 400;
            $result['reference_id'] = $m[1];
            $msg = $m[4];

            // Potong message jika ada "Saldo" atau "@"
            $msg = preg_split('/(Saldo|@)/i', $msg)[0];
            $result['message'] = trim($msg);

            return $result;
        }

        $result = [
            'status_code' => 201,
            'transaction_id' => null,
            'reference_id' => null,
            'destination' => null,
            'transaction_status' => 'process',
            'product' => [
                'code' => null,
                'detail' => null,
                'amount' => 0,
                'price' => 0,
            ],
        ];

        if (preg_match('/T#(\d+)/', $text, $m)) $result['transaction_id'] = $m[1];
        if (preg_match('/R#([\w\-]+)/', $text, $m)) $result['reference_id'] = $m[1];

        // Format A1 / A2
        if (preg_match('/R#([\w\-]+)\s+(.*?)\s+([A-Z0-9]+)\.(\d+).*QTY\s*:\s*(\d+).*?-\s*([\d.]+)\s*=/i', $text, $m)) {
            $result['reference_id'] = $m[1];
            $result['product']['detail'] = $m[2];
            $result['product']['code'] = $m[3];
            $result['destination'] = $m[4];
            $result['product']['amount'] = (int)str_replace('.', '', $m[5]);
            $result['product']['price'] = (int)str_replace('.', '', $m[6]);
            $result['transaction_status'] = 'process';
        }

        if (preg_match('/T#(\d+)\s+R#([\w\-]+)\s+(.+?)\s+([A-Z]+\d*)\.(\d+)\s+akan diproses.*?-\s*([\d.]+)\s*=/i', $text, $m)) {
            $result['status_code'] = 201;
            $result['transaction_id'] = $m[1];
            $result['reference_id'] = $m[2];
            $result['product']['detail'] = trim($m[3]);
            $result['product']['code'] = $m[4];
            $result['destination'] = $m[5];
            $result['product']['amount'] = (int) str_replace('.', '', preg_match('/([\d.]+)$/', $m[3], $amt) ? $amt[1] : 0); // 1000
            $result['product']['price'] = (int) str_replace('.', '', $m[6]); // 1321
            $result['transaction_status'] = 'process';
            return $result;
        }

        if (preg_match('/T#(\d+)\s+R#([\w\-]+)\s+(.*?)\s+([A-Z0-9]+)\.(\d+)\s*,\s*QTY\s*:\s*(\d+)\s+akan diproses\.\s+Saldo\s+[\d\.]+\s*-\s*([\d\.]+)\s*=/i', $text, $m)) {
            $result['transaction_id'] = $m[1]; // 762261897
            $result['reference_id'] = $m[2]; // 7777
            $result['product']['detail'] = trim($m[3]); // H2H DANA Topup (Bebas Nominal)
            $result['product']['code'] = $m[4]; // BBSDN
            $result['destination'] = $m[5]; // 085736044280
            $result['product']['amount'] = (int) $m[6]; // 12345
            $result['product']['price'] = (int) str_replace('.', '', $m[7]); // 12516
            $result['transaction_status'] = 'process';
            return $result;
        }

        return [
            'status_code' => 422,
            'message' => 'Unprocessable response format',
            'raw' => $text,
        ];
    }

    public static function parseCallback($text)
    {
        $result = [
            'status_code' => 200,
            'transaction_id' => null,
            'reference_id' => null,
            'destination' => null,
            'product' => [
                'code' => null,
                'detail' => null,
                'amount' => 0,
            ],
            'transaction_status' => null,
        ];

        if (preg_match('/T#(\d+)\s+R#([\w\-]+)\s+(.+?)\s+(\w+)\.(\d+)\s+SUKSES\. SN\/Ref: ([\w\.]+)\./i', $text, $m)) {
            $result['transaction_status'] = 'success';
            $result['transaction_id'] = $m[1];
            $result['reference_id'] = $m[2];
            $result['product']['detail'] = trim($m[3]);
            $result['product']['code'] = $m[4];
            $result['destination'] = $m[5];
            $result['serial_number'] = $m[6];

            if (preg_match('/(\d{1,3}(?:\.\d{3})*)/', $result['product']['detail'], $am)) {
                $result['product']['amount'] = (int) str_replace('.', '', $am[1]);
            }

            if (preg_match('/Saldo [\d\.]+ - ([\d\.]+) = [\d\.]+/', $text, $price)) {
                $result['product']['price'] = (int) str_replace('.', '', $price[1]);
            }

            return $result;
        }

        if (preg_match('/T#(\d+)\s+R#([\w\-]+)\s+(.+?)\s+([A-Z]+\d*)\.(\d+)\s+GAGAL\.\s+(.+?)\.\s+Saldo/i', $text, $m)) {
            $result['transaction_id'] = $m[1];
            $result['reference_id'] = $m[2];
            $result['product']['detail'] = trim($m[3]);
            $result['product']['code'] = $m[4];
            $result['destination'] = $m[5];
            $result['product']['amount'] = (int) str_replace('.', '', preg_match('/([\d.]+)$/', $m[3], $amt) ? $amt[1] : 0);
            $result['transaction_status'] = 'failed';
            $result['message'] = trim($m[6]);
            return $result;
        }

        return [
            'status_code' => 422,
            'message' => 'Unprocessable response format',
            'raw' => $text,
        ];
    }

    public static function parseCheck($text)
    {
        $result = [
            'status_code' => 200,
            'reference_id' => null,
            'destination' => null,
            'product' => [
                'code' => null,
                'detail' => null,
                'amount' => 0,
                'price' => 0,
            ],
            'transaction_status' => null,
        ];

        if (preg_match('/R#([\w\-]+)\s+(.+?)\s+(\w+)\.(\d+)\s+sudah pernah.*?status Sukses\. SN: ([\w\.]+).*?Hrg ([\d\.]+)/i', $text, $m)) {
            $result['transaction_status'] = 'success';
            $result['reference_id'] = $m[1];
            $result['product']['detail'] = trim($m[2]);
            $result['product']['code'] = $m[3];
            $result['destination'] = $m[4];
            $result['serial_number'] = $m[5];
            $result['product']['price'] = (int) str_replace('.', '', $m[6]);

            if (preg_match('/(\d{1,3}(?:\.\d{3})*)/', $result['product']['detail'], $am)) {
                $result['product']['amount'] = (int) str_replace('.', '', $am[1]);
            }

            return $result;
        }

        if (preg_match('/R#([\w\-]+)\s+(.+?)\s+(\w+)\.(\d+)\s+sudah pernah.*?status Gagal\. (.+?)\. Hrg ([\d.]+)/i', $text, $m)) {
            $result["transaction_status"] = "failed";
            $result["reference_id"] = $m[1];
            $result["product"]["detail"] = trim($m[2]);
            $result["product"]["code"] = $m[3];
            $result["destination"] = $m[4];
            $result["product"]["amount"] = (int) str_replace('.', '', preg_match('/([\d.]+)$/', $m[2], $amt) ? $amt[1] : 0);
            $result["product"]["price"] = (int) str_replace('.', '', $m[6]);
            $result["message"] = trim($m[5]);
            return $result;
        }

        if (preg_match('/TIDAK ADA transaksi Tujuan (\d+) pada tgl .*? Tidak ada data\./i', $text, $m)) {
            return [
                'status_code' => 404,
                'message' => 'Tidak ada data transaksi',
            ];
        }

        if (preg_match('/T#(\d+)\s+R#([\w\-]+)\s+(\w+)\.(\d+)\s+@[\d:]+, status Menunggu Jawaban\./i', $text, $m)) {
            return [
                'status_code' => 200,
                'transaction_id' => $m[1],
                'reference_id' => $m[2],
                'transaction_status' => 'process',
                'product' => ['code' => $m[3]],
                'destination' => $m[4],
            ];
        }

        return [
            'status_code' => 422,
            'message' => 'Unprocessable response format',
            'raw' => $text,
        ];
    }

    public static function parseBalance($text)
    {
        $result = [
            'status_code' => 200,
            'balance' => 0,
        ];

        if (preg_match('/Saldo\s+([\d\.]+)/i', $text, $m)) {
            $result['balance'] = (int) str_replace('.', '', $m[1]);
            return $result;
        }

        return [
            'status_code' => 422,
            'message' => 'Unprocessable response format',
            'raw' => $text,
        ];
    }

    /**
     * Parse transaction response based on the state
     *
     * @param string $text The response text to parse
     * @param string $state The state of the transaction (create, callback, check, balance)
     * @return array Parsed transaction details
     */
    public static function responseParser($text, $state)
    {
        if ($text == "Too short" || $text == 'Pengguna tidak ditemukan') {
            return ['status_code' => 401, 'message' => 'Transaction cannot be authorized with the current merchant ID'];
        }
        if (preg_match('/\bPin Salah\b/i', $text)) {
            return ['status_code' => 401, 'message' => 'Transaction cannot be authorized with the current Merchant Pin'];
        }
        if (preg_match('/\bPassword Salah\b/i', $text)) {
            return ['status_code' => 401, 'message' => 'Transaction cannot be authorized with the current Account Password'];
        }
        if (preg_match('/^IP tidak sesuai @[\d\.]+$/i', $text, $matches)) {
            return ['status_code' => 401, 'message' => $text];
        }

        switch ($state) {
            case 'create':
                return Self::parseCreate($text);
            case 'callback':
                return Self::parseCallback($text);
            case 'check':
                return Self::parseCheck($text);
            case 'balance':
                return Self::parseBalance($text);
            default:
                throw new Exception("Unknown parser state: " . $state);
        }
    }

    public static function validator($array)
    {
        $result = [
            'status_code' => 400,
            'message' => "One or more parameters is invalid.",
            'validation_message' => []
        ];
        foreach ($array as $key => $value) {
            if (empty($value)) {
                $result['validation_message'][$key] = "$key is required";
            } elseif ($key == 'amount') {
                if (!is_numeric($value)) {
                    $result['validation_message'][$key] = "$key must be numeric";
                } elseif ($value < 1) {
                    $result['validation_message'][$key] = "$key must be greater than 0";
                }
            } elseif ($key == 'destination') {
                if (!preg_match('/^[0-9]+$/', $value)) {
                    $result['validation_message'][$key] = "$key must be numeric";
                } elseif (strlen($value) < 10) {
                    $result['validation_message'][$key] = "$key must be at least 10 digits";
                } elseif (strlen($value) > 14) {
                    $result['validation_message'][$key] = "$key must be at most 15 digits";
                }
            }
        }

        // if (preg_match('/^BBS/', $array['product_code'])) {
        //     if (empty($array['amount'])) {
        //         $result['validation_message']['amount'] = "amount is required";
        //     } elseif ($array['amount'] < 10000) {
        //         $result['validation_message']['amount'] = "amount must be greater than 10000";
        //     }
        // }

        if (count($result['validation_message']) > 0) {
            return $result;
        } else {
            return ['status_code' => 200, 'message' => 'OK'];
        }
    }
}
