<?php
declare(strict_types=1);
namespace App;

use Exception;

/**
 * TransactionService
 * ------------------
 * PURPOSE:
 * - Business logic for handling transactions.
 * - Handles WITHDRAW: subtract money from a card + insert a row in `transaction`.
 * - Ensures CONSISTENCY using SQL transactions and row locking.
 * - Prevents DOUBLE CHARGES with Idempotency_Key.
 */

final class TransactionService{
    /**
     * withdraw()
     * ----------
     * @param int         $cardId      -> Card to withdraw from (Card_ID column)
     * @param string      $product     -> Course ID (stored in Product column)
     * @param string      $idemKey     -> Idempotency_Key (prevents double charging)
     * @param string      $Amount_taken -> the money taken from the account
     *
     * @return array Result info (success/fail, balances, ids…)
     */

    public static function withdraw(
        int $cardId,
        string $product,
        string $idemkey,
        float $Amount_taken)
        {
            //Get a PDO connection(via Database.php)
            $pdo= database::pdo();

            try{
                // Start atomic transaction (everything succeeds or nothing
                //You are telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
                $pdo->beginTransaction();

                //Idempotency: if this idem key was used before, return that row (no double charge)
                $check=$pdo->prepare('SELECT * FROM `transaction` WHERE `Idempotency_Key` = ? LIMIT 1');
                $check->execute([$idemkey]);
                if($existing=$check->fetch()){
                    $pdo->commit();
                    return[
                        'idempotent' => true,
                        'transaction' => $existing,
                        'note' => 'Same Idempotency_Key used; returning previous result.'
                    ];
                }

                //Insert a transaction row; triggers will set Balance_After + status and update card if success
                $insert=$pdo->prepare(' INSERT INTO `transaction`
                (`Card_ID`, `Product`, `Amount_taken`, `type`, `Idempotency_Key`)
                VALUES (?, ?, ?, "withdraw", ?)');
                $insert->execute([$cardId, $product,$Amount_taken, $idemkey]);

                //returns the auto-increment ID of the last inserted row in this connection.
                $autoID=(int)$pdo->lastInsertId();
                //Query the row back
                $get=$pdo->prepare('SELECT * FROM `transaction` WHERE `ID`= ?');
                $get->execute([$autoID]);
                $row=$get->fetch();

                $pdo->commit();

                return[
                    'status'           =>$row['status'],
                    'card_id'          =>(int)$row['Card_ID'],
                    'type'             =>$row['type'],
                    'Amount_taken'     =>(float)$row['Amount_taken'],
                    'Balance_After'    =>(float)$row['Balance_After'],
                    'Product'          =>$row['Product'],
                    'Transaction_ID'   =>$row['Transaction_ID'],
                    'Idempotency_key'  =>$row['Idempotency_Key']
                ];
            } catch(Exception $e){
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                    Json::error(500,'Transaction failed', ['details' => $e->getMessage()]);
                    return [];
                }
                return[];
            }


    }


}
?>