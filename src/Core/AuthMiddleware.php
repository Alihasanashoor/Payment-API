<?php 
declare(strict_types=1);

namespace App\Core;

use App\Auth;

/**
 * Authentication middleware.
 *
 * Enforces API key authentication for incoming requests.
 *
 * This middleware is responsible ONLY for authentication concerns.
 * It validates the presence and correctness of the API key and
 * terminates the request immediately if authentication fails.
 *
 * No business logic or rate limiting should be implemented here.
*/


final class AuthMiddleware implements Middleware
{
    
    /**
     * Executes authentication checks for the current request.
     *
     * Delegates API key validation to the Auth service.
     * On failure, the request lifecycle is terminated
     * with an appropriate HTTP error response.
    */

    public function handle(): void
    {
        Auth::requireApiKey();
    }
}


?>