<?php

declare(strict_types=1);
namespace App;
use App\Json;
use App\Database;

/**
 * Auth
 *
 * Handles API-key based authentication for machine-to-machine requests.
 * 
 * Security model:
 * - API keys are generated once and shown to the client one time only
 * - Only hashed keys are stored in the database
 * - Each key represents an identity (service / client)
 * - Keys can be revoked or expired without redeploying the app
 */

final class Auth{
    /**
    * Require a valid API key.
    *
    * This method:
    * 1. Extracts the API key from the request header
    * 2. Hashes it (never trusting raw secrets)
    * 3. Validates it against the database
    * 4. Enforces revocation and expiration rules
    * 5. Returns the key identity for auditing and authorization
    *
    * @return array The authenticated API key record (identity)
    */
    public static function requireApiKey(): array{
        /**
        * Read API key from HTTP headers.
        *
        * PHP converts:
        *   X-API-Key: abc
        * into:
        *   $_SERVER['HTTP_X_API_KEY']
        *
        * If the header is missing, the request is unauthorized.
        */
        $providedKey=$_SERVER['HTTP_X_API_KEY'] ?? '';

        if($providedKey ==''){
            Json::error(401, 'Missing API key');
        }
        /**
        * Hash the provided API key.
        * - We never store or compare raw API keys
        * - Hashing ensures that a database leak does NOT expose usable secrets
        * This works exactly like password verification.
        */
        $keyHash= hash('sha256', $providedKey);

        /**
        * Look up the API key identity in the database.
        * Security rules enforced here:
        * - Key must exist
        * - Key must be active (not revoked)
        * - Key must not be expired
        *
        * These checks are done in SQL so that authentication
        * is data-driven, not hardcoded.
        */
        $pdo=database::pdo();

        $statement = $pdo->prepare(
            'SELECT * FROM api_keys
            WHERE key_hash = ?
            AND is_active = 1
            AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1'
        );
        
        /**
        * Execute the query with the provided key hash.
        * Prepared statements prevent SQL injection.
        */
        $statement->execute([$keyHash]);

        /**
        * Fetch the API key record.
        * - Returns the key row if valid
        * - Returns false if the key is missing, inactive, or expired
        */
        $apikey=$statement->fetch();

        /**
        * If no valid key is found, deny access.
        *
        * do NOT reveal whether the key was:
        * - invalid
        * - revoked
        * - expired
        *
        * This avoids leaking security details to attackers.
        */
        if(!$apikey){
            Json::error(401, 'Invalid or expired API key');
        }

        /**
        * Return the authenticated identity.
        *
        * Returning the key record allows downstream code to:
        * - Know which service initiated the request
        * - Attach initiator identity to transactions
        * - Apply per-client rules or limits
        *
        * Authentication is not just "allowed / denied" —
        * it is identity-aware.
        */
        return $apikey;



    }
}

?>