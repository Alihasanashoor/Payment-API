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
}

?>