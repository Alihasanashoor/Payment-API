<?php 
declare(strict_types=1);

namespace App\Core;

/**
 * Middleware contract.
 *
 * Represents a request-processing component executed
 * as part of the HTTP middleware pipeline.
 *
 * Implementations are responsible for cross-cutting concerns
 * (e.g. authentication, rate limiting, security enforcement)
 * and must either allow the request to proceed or
 * terminate execution by emitting an HTTP response.
 *
 * Middleware MUST be side-effect safe and MUST NOT
 * contain business logic.
*/

interface Middleware{
    /**
     * Executes middleware logic for the current request.
     *
     * Implementations should fail fast and stop execution
     * on violation (e.g. unauthorized access, limit exceeded).
    */

    public function handle():void;

}



?>