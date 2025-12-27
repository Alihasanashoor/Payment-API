<?php 
declare(strict_types=1);
namespace App;

/**
 * PURPOSE:
 *   - This class validates input from HTTP requests.
 *   - Makes sure JSON is parsed, required fields exist, values are correct.
 *   - Prevents garbage or malicious input from reaching your DB.
 */
final class Validator{


    /**
     * jsonBody()
     * - Reads the raw request body ("php://input" stream).
     * - Tries to decode JSON into a PHP array.
     * - If the request isn’t valid JSON, stops with error 400.
     */
    public static function jsonBody(){
        $raw=file_get_contents('php://input'); // raw string body
        $data=json_decode($raw,true); // decode JSON to array
        if(!is_array($data)){                    // if failed
            Json::error(400,'Invalid JSON body'); // stop with error
        }
        return $data;                                            // return array for further use
    }

    /**
     * required()
     * make sure certain key exist in the array
     * Example: reuire ["card_id", "amount"] if missing we get error 422
     */

    public static function required(array $data, array $keys){
        foreach($keys as $k){
            if(!array_key_exists($k,$data)){
                Json::error(422,"Missing: $k");
            }
        }
    }

    /**
     * positiveAmount()
     * makes sure the amount/balance is not less then 0
     * prevents negative withdrawals or not number values such as "abc"
     */

    public static function positiveAmount($balance): void{
        if(!is_numeric($balance) || $balance <=0 ){
            Json::error(422,'amount must be grater then 0');
        }
    }

    /**
     * nonNegativeBalance()
     * makes sure the amount/balance is not less then 0
     * prevents negative balance inserted in database
     */

    public static function nonNegativeBalance($balance){
        if(!is_numeric($balance) || $balance <0 ){
            Json::error(422, 'initial balance cannot be negative');
        }
    }

    /**
     * indempotencykey()
     * ensure that indempotency_key exists 
     * this key is used to prevent double-chargini when client retries.
     * also enforces a max length (64 chars)
     */

    public static function idempotencykey(?string $idempotency_Key){
        if(!$idempotency_Key){
            Json::error(422,'idempotency_key is required');
        }
        if(strlen($idempotency_Key)>64){
            Json::error(422,'idempotency_key too long');
        }
    }

    /**
     * Phone_number()
     * ensure that Phone_number exists 
     * also enforces a max length (max 15 char and min 7 )
     */

    public static function validatePhoneNumber(string $number){
        // Trim spaces to removes leading spaces, leading spaces, and tabs / newlines
        $number = trim($number);

        if($number == ''){
            Json::error(422, 'phone number is required');
        }
        // Length validation
        if(strlen($number)<7){
            Json::error(422,'phone number too short');
        }
        if(strlen($number)>15){
            Json::error(422,'phone number too long');
        }
        
        if(!preg_match('/^\+?[0-9]+$/', $number)){
            Json::error(422, 'phone number must contain digits only');
        }
    }

    /**
     * email()
     * if link_ID dose not exsits its valid no need to check anything 
     * this key is used for students
     * also enforces a min len is 7
     */

    public static function validateEmail(string $email){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            Json::error(422, 'invalid email address');
        }
    }


    /**
     * link_ID()
     * if link_ID dose not exsits its valid no need to check anything 
     * this key is used for students
     * also enforces a min len is 7
     */

    public static function validateLinkID($link_ID){
        if($link_ID == null || $link_ID == ''){
            return;
        }

        // Trim spaces to removes leading spaces, leading spaces, and tabs / newlines
        $link_ID= trim($link_ID);

        if(strlen($link_ID) !== 3){
            Json::error(422, 'link_id must be exactly 3 characters');
        }

        if (!preg_match('/^[0-9]+$/i', $link_ID)) {
        Json::error(422, 'link_id format is invalid');
        }

}
}

?>