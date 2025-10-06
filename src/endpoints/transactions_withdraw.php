<?php

declare(strict_types=1);

use App\Validator;
use App\Json;
use App\TransactionService;

//This endpoint handles withdrawal transactions.

/**
 * POST /v1/transactions/withdraw
 * --------------------------------
 * This endpoint handles withdrawal transactions.
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
 * - Delegates to TransactionService::withdraw(), which:
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
$headers = function_exists('getallheaders') ? getallheaders() : [];
// Extract the Idempotency-Key header from the request, checking multiple possible casings
$headerKey =
    $headers['Idempotency-Key'] ??
    $headers['idempotency-key'] ??
    $headers['IDEMPOTENCY-KEY'] ?? null;

if (empty($data['idempotency_key']) && $headerKey) {
    $data['idempotency_key'] = $headerKey;
}
//Ensure all required fields are present in the request.
//If any are missing, Validator::required() will stop and return HTTP 422.
Validator::required($body,['card_id', 'Amount_taken','product','idempotency_key']);

//Ensure the `amount` field is a positive number.
Validator::positiveAmount($body['Amount_taken']);
Validator::idempotencyKey($body['idempotency_key']);


//Cast each field into the correct PHP type for safety.
$cardId= (int)$body['card_id'];
$amount= (float) $body['Amount_taken'];
$product= (string) $body['product'];
$idemkey= (string) $body['idempotency_key'];

//This calls TransactionService::withdraw(), which interacts with the DB.
$result = TransactionService::withdraw($cardId, $product, $idemkey,$amount);

//Decide which HTTP status code to return, based on the result:
//status comes from the database trigger (usually success)
//return HTTP 201 Created

if(($result['status'] ?? null) == 'success'){
    Json::ok(201,$result);
}

//Duplicate request (idempotency key reused)
//"idempotent" is true if TransactionService recognized a repeated key.
//Return HTTP 200 OK, with the previous transaction details

if(($result['idempotent'] ?? false)==true){
    Json::ok(200,$result); //returning previous transaction
}

//Any other situation (such as insufficient funds, failed trigger)
//Return HTTP 409 Conflict, meaning "we understood the request, but couldn’t complete it."
Json::ok(409,$result);





?>