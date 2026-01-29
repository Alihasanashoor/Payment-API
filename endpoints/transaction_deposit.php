<?php 

declare(strict_types=1);

use App\Auth;
use App\Validator;
use App\Json;
use App\Repository\CardRepository;
use App\TransactionService;
use App\Util\mask;



/**
 * POST /v1/transactions/deposit
 * --------------------------------
 * This endpoint handles deposit transactions.
 *
 * EXPECTED INPUT (in the HTTP request body, JSON format):
 * {
 *   "card_id": 914,                  // integer: which card to withdraw from
 *   "amount":  50.75,                // float: how much money to withdraw
 *   "product": "cs100",        // string: what the money is for (e.g. course ID)
 *   "idempotency_key": "abc123"      // string: unique per attempt to prevent double charges
 * }
 *
 * BEHAVIOR:
 * - Validates input JSON (required fields, amount > 0).
 * - Delegates to TransactionService::deposit(), which:
 *      • Starts a DB transaction
 *      • Checks if idempotency key already exists
 *      • If so, returns the previous transaction instead of creating a new one
 *      • Otherwise, inserts a new row in `transactions`
 *      • DB triggers calculate Balance_After, update the card balance, and set status
 * - Returns a JSON response with the right HTTP status code.
 */

//Parse the raw request body as JSON.
//If the body is not valid JSON, Validator::jsonBody() will throw an error response.
$body=Validator::jsonBody();

// Get all HTTP request headers (use getallheaders() if available, else an empty array)
$headers = function_exists('getallheaders') ? getallheaders(): [];

//Ensure all required fields are present in the request.
//If any are missing, Validator::required() will stop and return HTTP 422.
Validator::required($body,['card_number','Amount', 'idempotency_key']);

// Ensure the `amount` is positive number.
Validator::positiveAmount($body['Amount']);

//ensure that idempotency_key exists, also enforces a max length (64 chars)
Validator::idempotencykey($body['idempotency_key']);



// Cast each field into the correct PHP type for safety.
$CardNumber =(string)$body['card_number'];
$amount =(float)$body['Amount'];
$idemkey= (string) $body['idempotency_key'];

/**
 * Resolve sender card using card number
 * ------------------------------
 * Converts card number into internal Card_ID.
 * If the card number does not exist, stop immediately.
 */
$card_number = CardRepository::getIdByCardNumber($CardNumber);
if(!$card_number){
    Json::error(404, 'card number not found');
}

// This calls TransactionService::deposit(), which interacts with the DB.
$result = TransactionService::deposit($card_number, $idemkey, $amount );

//Decide which HTTP status code to return, based on the result:
//status comes from the database trigger (usually success)
//return HTTP 201 Created
if(($result['status'] ?? null) == 'success'){
    Json::ok(201,[
    'status'        =>  'success',
    'amount'        =>  $result['amount'],
    'card_number'   =>  mask::iban_mask($CardNumber),
    'type'          =>  $result['type'],
    'Balance_After' =>  $result['Balance_After'],
    'created_at'    =>  $result['created_at'],
]);
}


//Any other situation (such as insufficient funds, failed trigger)
//Return HTTP 409 Conflict, meaning "we understood the request, but couldn’t complete it."
Json::ok(409,$result);




?>