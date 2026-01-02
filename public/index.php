<?php 

declare(strict_types=1);
// CORS (allow cross-origin calls during dev)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');
header('Access-Control-Allow-Credentials: false');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-API-Key, Content-Type');
// Handle preflight quickly
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';


//load bootstrap & helpers
require_once __DIR__ . '/../src/env.php';           //loads .env variables and sets JSON Content-Type header
require_once __DIR__ . '/../src/Json.php';          //helpers to return JSON consistently
require_once __DIR__ . '/../src/Auth.php';          //API key enforcement
require_once __DIR__ . '/../src/database.php';      //PDO connection factory
require_once __DIR__ . '/../src/Validator.php';     //input validation helpers
require_once __DIR__ . '/../src/Service/TransactionService.php';    //business logic (transactions, student lookup, etc.)
require_once __DIR__ . '/../src/Service/StudentService.php';    
require_once __DIR__ . '/../src/Service/AccountService.php';    
require_once __DIR__ . '/../src/Service/TransactionServiceV1.php';

use App\Json;
use App\Auth;



/**
 * FRONT CONTROLLER / ROUTER
 * ------------------------
 * This file is the single entry point for all API requests.
 *
 * Responsibilities:
 * - Read HTTP method (GET, POST, etc.)
 * - Parse request path (URI)
 * - Route the request to the correct endpoint file
 * - Return a JSON 404 error if no route matches
 */

// Extract the HTTP method (GET, POST, PUT, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// Parse only the path from the full URL (ignore query string)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


/**
 * HEALTH CHECK / PING
 * ------------------
 * Used to verify that:
 * - API is running
 * - Routing is working
 * - Server is reachable
 */

if($method=='GET' && $path=='/v1/ping'){
    Json::ok(200,['ok'=> true,'service' => 'payment-api']);
}
/**
 * STUDENT RESOLUTION
 * ------------------
 * Resolves a student by Link_ID (session user ID).
 * Used internally by Academic Registration before payment.
 */

if($method =='GET' && $path=='/v1/students/resolve'){
    require __DIR__ . '/../endpoints/students_resolve.php';
    exit;
}

/**
 * WITHDRAW (V1)
 * -------------
 * Academic Registration System withdrawal endpoint.
 * Triggered when a student clicks "Pay" for a course.
 *
 * Uses:
 * - Student resolution
 * - Idempotency protection
 * - Database triggers for balance updates
 */
if($method == 'POST' && $path =='/v1/transactions/withdraw'){
    require __DIR__ . '/../endpoints/AcademicRegistrationSystem_Withdraw.php';
    exit;
}

/**
 * WITHDRAW (V2)
 * -------------
 * Newer or alternative withdrawal implementation.
 * Allows versioned evolution of the API without breaking V1.
 */

if($method == 'POST' && $path =='/v2/transactions/withdraw'){
    require __DIR__ . '/../endpoints/transactions_withdraw.php';
    exit;
}

/**
 * DEPOSIT
 * -------
 * Adds funds to a card/account.
 * Typically used by admin systems or funding services.
 */

if($method == 'POST' && $path == '/v2/transactions/deposit'){
    require __DIR__ . '/../endpoints/transaction_deposit.php';
    exit;
}

/**
 * ACCOUNT CREATION
 * ----------------
 * Creates a new payment account and card.
 * Called when a student registers for the first time.
 */

if($method== 'POST' && $path == '/v2/Account/create_Account'){
    require __DIR__ . '/../endpoints/Create_Account.php';
    exit;
}

/**
 * CARD-TO-CARD TRANSFER
 * --------------------
 * Handles money transfer between two cards using IBAN resolution
 * and atomic double-entry transactions.
 */

if($method == 'POST' && $path == '/v2/Account/transfer'){
    require __DIR__ . '/../endpoints/transfer.php';
    exit;
}

/**
 * FALLBACK: ROUTE NOT FOUND
 * ------------------------
 * If no route matches the method + path combination,
 * return a standardized JSON 404 error.
*/

Json::error(404,"Not found: $method $path");

?>

