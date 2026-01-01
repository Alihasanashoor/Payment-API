<?php
declare(strict_types=1);
use App\Auth;
use App\Json;
use App\StudentService;

// Enforce API key authentication for this request
Auth::requireApiKey();

//PURPOSE: Fetch a student’s account + card info by their numeric Link_ID.

//Read raw value (string) and trim. We keep it as string to preserve leading zeros.
$linkIdRaw= $_GET['link_id']?? null;
$linkId= is_string($linkIdRaw) ? trim($linkIdRaw) : '';


//validate input, if link_id is 0 return HTTP 400.
if($linkId ==''){
    Json::error(400, 'Missing or invalid query parameter: link_id');
    exit;
}


//Fetch student’s account + first card using the service layer, Service returns null if no row is found.
$result= StudentService::getByLinkid($linkId);

if ($result === null || empty($result['card_id'])) {
    // If account exists but no card, you can choose to 404, or auto-create a card.
    Json::error(404, 'not found for this link_id or no card for this account');
    exit;
}



//if Success return HTTP 200 OK with normalized JSON payload.

Json::ok(200,[
    'account_id' => $result['account_id'],
    'card_id'    => $result['card_id'],
    'balance'    => $result['balance'],
    'link_id'    => $result['link_id'], 
]);

 
?>