<?php
declare(strict_types=1);
namespace App;

use Exception;

/**
 * TransactionServiceV1
 * --------------------
 * PURPOSE:
 * - Handles financial transactions for the Academic Registration system.
 * - Responsible for WITHDRAW operations when a student pays for a course.
 *
 * DESIGN NOTES:
 * - Uses database transactions to ensure ACID compliance.
 * - Uses Idempotency_Key to prevent duplicate charges.
 * - Relies on MySQL triggers to:
 *      • Update card balance
 *      • Set transaction status
 *      • Calculate Balance_After
 *
 * FLOW:
 *  Student clicks "Pay"
 *      → StudentService resolves student & card
 *      → TransactionServiceV1::withdraw()
 *      → Database triggers finalize the operation
 */

final class TransactionServiceV1{
    /**
     * Withdraws money from a card and records the transaction.
     * ----------
     * @param int         $cardId      -> Card to withdraw from (Card_ID column)
     * @param string      $product     -> Course ID or product reference
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
            // Obtain a PDO connection from the database factory
            $pdo= database::pdo();

            try{
                 /**
                    * Begin an atomic database transaction.
                    * Nothing is permanently written unless ALL steps succeed.
                */
                $pdo->beginTransaction();

                /**
                    * IDEMPOTENCY CHECK
                    * -----------------
                    * If this Idempotency_Key already exists, it means:
                    * - The request was already processed
                    * - We MUST NOT charge again
                    * Instead, return the previous transaction safely.
                */
                $check=$pdo->prepare('SELECT * FROM `transaction` WHERE `Idempotency_Key` = ? LIMIT 1');
                $check->execute([$idemkey]);
                
                if($existing=$check->fetch()){
                    // End transaction safely (no changes made)
                    $pdo->commit();
                    return[
                        'idempotent' => true,
                        'transaction' => $existing,
                        'note' => 'Same Idempotency_Key used; returning previous result.'
                    ];
                }

                 /**
                    * INSERT TRANSACTION
                    * ------------------
                    * Only minimal data is inserted here.
                
                    * IMPORTANT:
                    * - MySQL triggers handle:
                    *• balance validation
                    *• balance deduction
                    *• transaction status
                    *• Balance_After calculation
                */
                $insert=$pdo->prepare(' INSERT INTO `transaction`
                (`Card_ID`, `Product`, `Amount_taken`, `type`, `Idempotency_Key`)
                VALUES (?, ?, ?, "withdraw", ?)');
                $insert->execute([$cardId, $product,$Amount_taken, $idemkey]);



                /**
                    * Fetch the auto-generated transaction ID
                    * (used to retrieve the finalized row after triggers run)
                */
                $autoID=(int)$pdo->lastInsertId();

                /**
                    * Retrieve the completed transaction row
                    * (includes trigger-updated fields)
                */
                $get=$pdo->prepare('SELECT * FROM `transaction` WHERE `ID`= ?');
                $get->execute([$autoID]);
                $row=$get->fetch();

                 /**
                    * Commit the transaction:
                    * - Balance update is finalized
                    * - Transaction is permanently recorded
                */
                $pdo->commit();

                
                /**
                    * Return a clean API-friendly response
                    * (safe to expose to Academic Registration frontend)
                */
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
                /**
                    * ERROR HANDLING
                    * --------------
                    * If anything fails:
                    * - Roll back ALL changes
                    * - Prevent partial balance updates
                    * - Return a consistent API error
                */
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