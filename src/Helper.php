<?php

namespace Glaezz\Okeconenct;

use Exception;

class Helper
{

    public static function parseTransaction($text)
    {
        $result = [
            'transaction_id' => '',
            'reference_id' => '',
            'destination' => '',
            'product' => [
                'code' => '',
                'detail' => '',
                'amount' => 0,
                'price' => 0,
            ],
            
        ];

        // Pattern 1: Ada QTY
        $pattern1 = '/T#(\d+)\s+R#(\d+)\s+(.+?)\s+(\w+)\.(\d+)\s*,?\s*QTY\s*:\s*(\d+)\s*akan diproses\. Saldo [\d\.]+ - ([\d\.]+) = [\d\.]+ @[\d:]+/';

        // Pattern 2: Tanpa QTY
        $pattern2 = '/T#(\d+)\s+R#(\d+)\s+(.+?)\s+(\w+)\.(\d+)\s*akan diproses\. Saldo [\d\.]+ - ([\d\.]+) = [\d\.]+ @[\d:]+/';

        if (preg_match($pattern1, $text, $matches)) {
            $result['transaction_id'] = $matches[1];
            $result['reference_id'] = $matches[2];
            $result['destination'] = $matches[5];

            $result['product']['code'] = $matches[4];
            $result['product']['detail'] = trim($matches[3]);
            $result['product']['amount'] = (int) str_replace('.', '', $matches[6]);
            $result['product']['price'] = (int) str_replace('.', '', $matches[7]);
        } elseif (preg_match($pattern2, $text, $matches)) {
            $result['transaction_id'] = $matches[1];
            $result['reference_id'] = $matches[2];
            $result['destination'] = $matches[5];

            $result['product']['code'] = $matches[4];
            $result['product']['detail'] = trim($matches[3]);

            if (preg_match('/(\d{1,3}(?:\.\d{3})*)/', $matches[3], $amountMatch)) {
                $result['product']['amount'] = (int) str_replace('.', '', $amountMatch[1]);
            }

            $result['product']['price'] = (int) str_replace('.', '', $matches[6]);
        } else {
            throw new Exception("Failed to parse transaction details from the response.");
        }

        return $result;
    }
}
