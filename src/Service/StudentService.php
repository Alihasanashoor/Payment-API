<?php
declare(strict_types=1);
namespace App;

use PDO;

final class StudentService{
    /**
     * Resolve a student by their Link_ID (same as your $_SESSION['User_ID'])
     * Returns: ['account_id'=>int, 'card_id'=>int, 'balance'=>float, 'link_id'=>string] or null
     */
    public static function getByLinkid(string $linkId){
        //get PDO connection 
        $pdo=database::pdo();

        // Prepare the SQL query safely with PDO
        // SQL query:
        // 1. Select account_id, link_id, card_id, balance
        // 2. Join account → card so we can get both account info and associated card info.
        // 3. Filter by the given Link_ID (parameterized with :link to prevent SQL injection).
        // 4. Order by Card_ID ascending to get the "first" card if multiple exist.
        // 5. Limit to 1 result.
        $start=$pdo->prepare
        (  'SELECT
        a.`Account_ID` AS account_id,
        a.`Link_ID`    AS link_id,
        c.`Card_ID`    AS card_id,
        c.`Balance`    AS balance
        FROM `accounts` AS a
        JOIN `card` AS c ON c.`Account_ID` = a.`Account_ID`
        WHERE a.`Link_ID` = :link
        ORDER BY c.`Card_ID` ASC
        LIMIT 1'
        );
        

        // Bind parameter :link to the provided $linkId
        $start->execute([':link'=>$linkId]);

        // Fetch one row as an associative array (column aliases become keys)
        $row=$start->fetch(PDO::FETCH_ASSOC);

        //if no missing account/card found, return null
        if(!$row){
            return null;
        }

        //convert values into strict PHP types
        // This makes sure the API response or service return value has predictable types.
        return[
            'account_id' => (int)$row['account_id'],
            'card_id'    => (int)$row['card_id'],
            'balance'    => (float)$row['balance'],
            'link_id'    => (string)$row['link_id']
        ];
        

    }

}

?>