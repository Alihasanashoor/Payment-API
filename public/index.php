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


//load bootstrap & helpers
require_once __DIR__ . '/../src/env.php';           //loads .env variables and sets JSON Content-Type header
require_once __DIR__ . '/../src/Json.php';          //helpers to return JSON consistently
require_once __DIR__ . '/../src/Auth.php';          //API key enforcement
require_once __DIR__ . '/../src/database.php';      //PDO connection factory
require_once __DIR__ . '/../src/Validator.php';     //input validation helpers
require_once __DIR__ . '/../src/TransactionService.php';    //business logic (transactions, student lookup, etc.)
require_once __DIR__ . '/../src/StudentService.php';    

use App\Json;
use App\Auth;


/**
 * Read HTTP method and path.
 * REQUEST_URI includes path + query string (e.g., /v1/ping?x=1)
 * parse_url(..., PHP_URL_PATH) extracts just the path (/v1/ping)
 */
//Because when building an API, you usually care about the path (/v1/ping) to know which “endpoint” to run.

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if($method=='GET' && $path=='/v1/ping'){
    Json::ok(200,['ok'=> true,'service' => 'payment-api']);
}

if($method == 'POST' && $path =='/v1/transactions/withdraw'){
    require __DIR__ . '/../src/endpoints/transactions_withdraw.php';
    exit;
}

if($method =='GET' && $path=='/v1/students/resolve'){
    require __DIR__ . '/../src/endpoints/students_resolve.php';
    exit;
}

// If nothing matched, return 404
Json::error(404,"Not found: $method $path");

?>

