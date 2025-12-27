<?php
declare(strict_types=1);

namespace App\Util;

/**
 * mask
 * ----
 * Utility class responsible for masking sensitive strings.
 *
 * supports:
 * - IBAN masking for safe display and logging
 *
 * This helps:
 * - Prevent sensitive data leakage
 * - Comply with security and privacy best practices
 */

final class mask{
     /**
     * iban_mask()
     * -----------
     * Masks an IBAN by keeping only the first and last 4 characters visible.
     *
     * Example:
     *   Input:  BH29BMAG00001234567890
     *   Output: BH29****7890
     *
     * @param string $iban Full IBAN string
     *
     * @return string Masked IBAN safe for display
     */

    public static function iban_mask(string $iban){
        /**
         * If the IBAN is too short to mask safely,
         * return a fully masked placeholder.
         */
        if(strlen($iban) <=8){
            return '****';
        }
        /**
         * Build masked IBAN:
         * - First 4 characters are visible
         * - Middle part is replaced with ****
         * - Last 4 characters remain visible
         */
        return substr($iban,0,4) .
        '****' .
        substr($iban, -4);
    }
}

?>