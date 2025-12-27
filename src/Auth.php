<?php

declare(strict_types=1);
namespace App;

final class Auth{
    public static function requireApiKey(){
        /**
         * PHP turns HTTP headers into $_SERVER['HTTP_<NAME>']
         * So header "X-API-Key: abc" becomes $_SERVER['HTTP_X_API_KEY'] = "abc"
         */

        $provided=$_SERVER['HTTP_X_API_KEY'] ?? '';

        // Expected value loaded from .env by env.php earlier
        $expected=getenv('API_KEY')?: '';

        //hash_equals() is a constant-time string compare — safer than $a === $b
        //If no key set or mismatch -> 401 Unauthorized
        if($expected =='' || !hash_equals($expected,$provided)){
            \App\Json::error(401,'Unauthorized: invalid API key');
        }
    }
}

?>