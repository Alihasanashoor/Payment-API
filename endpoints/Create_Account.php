<?php
declare(strict_types=1);

use App\Validator;
use App\Json;
use App\Account;

//This endpoint handles new account creation

/**
 * POST /v1/Account/create_Account
 *----------------------------------
 * EXPECTED INPUT (in the HTTP request body, JSON format):
 * {
 *   "Name": jasam,                   // string: name of the new user
 *   "Phone_Number":  123456789,      // int: phone number of the new user
 *   "email": "abc@gmail.com",        // string: email of the new user
 *   "Link_ID": "" or "16"            // int: the id(number) of the Academic Registration website
 *   "balance: 10.0"   
 * }
 * 
 * BEHAVIOR:
 * - Validates the incoming JSON payload (name, phone number, email, link ID, initial balance).
 * - Calls Account::CreateAccount(), which:
 *     • Starts a database transaction to ensure atomicity
 *     • Inserts a new row into the `accounts` table
 *     • Creates an associated `card` record with the provided initial balance
 *     • Retrieves the newly created account and card records
 *     • Commits the transaction if all operations succeed
 * - If any step fails, the transaction is rolled back to prevent partial data writes
 * - Returns a structured JSON response containing:
 *     • Account information
 *     • Card information (card number and balance)
 * - On failure, returns an error response with an appropriate HTTP status code
 */

//Parse the raw request body as JSON.
//If the body is not valid JSON, Validator::jsonBody() will throw an error response.
$body=Validator::jsonBody();

// Get all HTTP request headers (use getallheaders() if available, else an empty array)
$headers = function_exists('getallheaders') ? getallheaders() : [];

//Ensure all required fields are present in the request.
//If any are missing, Validator::required() will stop and return HTTP 422.
Validator::required($body,['Name', 'Phone_Number', 'email', 'Link_ID', 'balance']);

//Ensure the `amount` field is a positive number.
Validator::nonNegativeBalance($body['balance']);

// ensure the phone number is valid with no letters or special character's
Validator::validatePhoneNumber($body['Phone_Number']);

// ensure the email is valid
Validator::validateEmail($body['email']);

// ensure the link id is 3 characters & and only digets not letters allowed , if empty it is valid  
Validator::validateLinkID($body['Link_ID']);


$Name= (string) $body['Name'];
$Phone_Number= (string)$body['Phone_Number'];
$email= (string)$body['email'];
$Link_ID= $body['Link_ID'] === ''? null: (int) $body['Link_ID'];
$balance= (float)$body['balance'];


// call Account::CreateAccount(), which interacts with the DB.
$result = Account::CreateAccount($Name, $Phone_Number, $email, $Link_ID, $balance );


//Decide which HTTP status code to return, based on the result:
//return HTTP 201 Created
if(($result['status'] ?? null) == 'success'){
    Json::ok(201,$result);
}



//Any other situation (such as failed trigger)
//Return HTTP 409 Conflict, meaning "we understood the request, but couldn’t complete it."
Json::ok(409,$result);




?>
