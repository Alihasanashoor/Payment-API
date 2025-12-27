<?php

namespace App\Repository;

use App\database;

/**
 * CardRepository
 * --------------
 * PURPOSE:
 * - Encapsulates all database queries related to cards.
 * - Provides a clean abstraction over raw SQL.
 *
 * DESIGN:
 * - Repository layer (no business logic here).
 * - Used by services that need card identifiers.
 */

final class CardRepository{
     /**
     * getIdByIban()
     * -------------
     * Retrieves the Card_ID associated with a given IBAN.
     *
     * @param string $iban IBAN provided by client or upstream service
     *
     * @return int|null Returns Card_ID if found, otherwise null
     */
    public static function getIdByIban(string $iban){
        // Obtain PDO connection from the database factory
        $pdo = database::pdo();

        /**
            * Prepare a parameterized query to prevent SQL injection.
            * LIMIT 1 ensures minimal data retrieval and better performance.
        */
        $search= $pdo->prepare('SELECT Card_ID FROM Card WHERE iban =? LIMIT 1');

        // Execute query with the provided IBAN
        $search->execute([$iban]);

        /**
            * fetchColumn():
            * - Returns the first column of the first row
            * - Returns false if no row is found
        */
        $id= $search->fetchColumn();
        
        /**
         * Normalize return value:
         * - Cast Card_ID to int if found
         * - Return null if no matching card exists
         */
        return $id !== false ? (int)$id: null;

        
    }
}


?>