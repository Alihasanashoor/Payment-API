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
        * getIdByColumn()
        * ----------------
        * Generic internal helper to fetch Card_ID by a unique card column.
        *
        * @param string $column Allowed column name (validated internally)
        * @param string $value  Value to search for
        *
        * @return int|null
    */
    private static function executeCardIdLookup(string $sql, string $value){
        
        // Obtain PDO connection from the database factory
        $pdo = database::pdo();

        /**
            * Prepare a parameterized query.
            * LIMIT 1 ensures minimal data retrieval and better performance.
        */
        $search= $pdo->prepare($sql);

        // Execute query with the provided IBAN
        $search->execute([$value]);

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

    /**
     * getIdByIban()
     * -------------
     * Retrieves the internal Card_ID associated with a given IBAN.
     *
     * Used when clients or upstream systems identify a card by IBAN.
     *
     * @param string $iban IBAN provided by the client
     *
     * @return int|null Returns Card_ID if found, otherwise null
     */
    public static function getIdByIban(string $iban): ?int {
        return self::executeCardIdLookup(
            'SELECT Card_ID FROM `card` WHERE iban = ? LIMIT 1',
            $iban
    );
    }

     /**
     * getIdByCardNumber()
     * -------------------
     * Retrieves the internal Card_ID associated with a card number.
     *
     * This is the primary lookup method for card-based transactions.
     *
     * @param string $cardNumber Card number provided by the client
     *
     * @return int|null Returns Card_ID if found, otherwise null
     */
    public static function getIdByCardNumber(string $cardNumber): ?int {
        return self::executeCardIdLookup(
            'SELECT Card_ID FROM `card` WHERE card_number = ? LIMIT 1',
            $cardNumber
    );
    }


    
}


?>