<?php
declare(strict_types=1);

use App\Auth;
use App\Validator;
use App\Json;
use App\TransactionService;
use App\Repository\CardRepository;
use App\Util\mask;




//Parse the raw request body as JSON.
//If the body is not valid JSON, Validator::jsonBody() will throw an error response.
$body=Validator::jsonBody();

// Get all HTTP request headers (use getallheaders() if available, else an empty array)
$headers = function_exists('getallheaders') ? getallheaders() : [];

// Ensure all required fields are present in the request.
// If any are missing, Validator::required() will stop and return HTTP 422.
Validator::required($body,['from_iban', 'to_iban', 'amount']);

// check iban if the same block it
Validator::differentFields($body,'from_iban','to_iban');

//Cast each field into the correct PHP type for safety.
$from_iban= (string)$body['from_iban'];
$to_iban= (string)$body['to_iban'];
$amount= (float)$body['amount'];





/**
 * Resolve sender card using IBAN
 * ------------------------------
 * Converts IBAN into internal Card_ID.
 * If the IBAN does not exist, stop immediately.
 */

$from_cardId= CardRepository::getIdByIban($from_iban);
if(!$from_cardId){
    Json::error(404, 'from Iban not found');
}


/**
 * Resolve receiver card using IBAN
 * --------------------------------
 * Ensures the destination card exists before transfer.
 */

$to_cardId= CardRepository::getIdByIban($to_iban);
if(!$to_cardId){
    Json::error(404, 'reciver Iban not found');
}

/**
 * Execute the transfer
 * --------------------
 * Delegates business logic to TransactionService.
 * This ensures:
 * - Atomicity
 * - Balance updates via triggers
 * - Proper transaction grouping
 */
$result= TransactionService::transfer($from_cardId, $to_cardId, $amount);


/**
 * Successful response
 * -------------------
 * - Returns transaction group ID
 * - Masks IBANs for security
 * - Includes timestamp for auditing
 */
Json::ok(201,[
    'status'      => 'success',
    'amount'      => $result['amount'],
    'from_iban'   => mask::iban_mask($from_iban),
    'to_iban'     => mask::iban_mask($to_iban),
    'created_at'  => $result['created_at'],
]);

?>