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
                // telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
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
                (`Card_ID`, `Product`, `Amount`, `type`,`Idempotency_Key`)
                VALUES (?, ?, ?, ?, ?)');
                $insert->execute([$cardId, $product,$Amount_taken,'withdraw', $idemkey]);

                //returns the auto-increment ID of the last inserted row in this connection.
                $autoID=(int)$pdo->lastInsertId();
                //Query the row back
                $get=$pdo->prepare('SELECT * FROM `transaction` WHERE `ID`= ?');
                $get->execute([$autoID]);
                $row=$get->fetch();

                $pdo->commit();

                return[
                    'Transaction_ID'   =>$row['Transaction_ID'],
                    'status'           =>$row['status'],
                    'card_id'          =>(int)$row['Card_ID'],
                    'type'             =>$row['type'],
                    'Amount_taken'     =>(float)$row['Amount'],
                    'Balance_After'    =>(float)$row['Balance_After'],
                    'Product'          =>$row['Product'],
                    'initiator_type'   =>$row['initiator_type'],
                    'initiator_id'     =>$row['initiator_id'],
                    'Idempotency_key'  =>$row['Idempotency_Key']
                ];
            } catch(Exception $e){
                // check if there active database transaction right now
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                    Json::error(500,'Transaction failed', ['details' => $e->getMessage()]);
                    return [];
                }
                return[];
            }


        }

    public static function deposit(
            int $cardId,
            string $product,
            string $idemkey,
            float $Amount_send,
            
        ){
            //Get a PDO connection(via Database.php)
            $pdo= database::pdo();
            try{
                // Start atomic transaction (everything succeeds or nothing
                // telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
                $pdo->beginTransaction();

                //idempotency: if this idem key was used before, return that raw (no double charge)
                $check=$pdo->prepare('SELECT * FROM `transaction` WHERE `Idempotency_Key` = ? LIMIT 1');
                $check->execute([$idemkey]);
                if($existing=$check->fetch()){
                    $pdo->commit();
                    return [
                        'idempotent' => true,
                        'transaction' => $existing,
                        'note' => 'Same Idempotency_Key used; returning previous result.'
                    ];
                }
                
                $insert=$pdo->prepare(' INSERT INTO `transaction`
                (`Card_ID`, `Product`, `Amount`, `type`,`Idempotency_Key`)
                VALUES (?, ?, ?, ?, ?)');
                $insert->execute([$cardId, $product,$Amount_send,'deposit', $idemkey]);

                // Return the auto-increment ID of the last inserted row in this connection.
                $autoID=(int)$pdo->lastInsertId();
                // Query the row back
                $get=$pdo->prepare('SELECT * FROM `transaction` WHERE `ID` = ?');
                $get->execute([$autoID]);
                $row=$get->fetch();

                $pdo->commit();

                return[
                    'Transaction_ID'   =>$row['Transaction_ID'],
                    'status'           =>$row['status'],
                    'card_id'          =>(int)$row['Card_ID'],
                    'type'             =>$row['type'],
                    'Amount_taken'     =>(float)$row['Amount'],
                    'Balance_After'    =>(float)$row['Balance_After'],
                    'Product'          =>$row['Product'],
                    'initiator_type'   =>$row['initiator_type'],
                    'initiator_id'     =>$row['initiator_id'],
                    'Idempotency_key'  =>$row['Idempotency_Key']
                ];

            } catch(Exception $e){
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                    Json::error(500,'Transaction failed', ['details' => $e->getMessage()]);
                
                }
                
            }
    }

    public static function transfer(
        int $fromCardId,
        int $toCardId,
        float $amount
    ){
        // Get PDO connection from database factory
         $pdo= database::pdo();


         try{
            /**
                * Start a database transaction.
                * Both transfer entries must succeed together
                * or the entire operation is rolled back.
            */
            $pdo->beginTransaction();

            //transaction_group_id is generated by the service layer.
            $groupId=bin2hex(random_bytes(16));

            /**
                * Generate a unique Idempotency Key for this transfer.
                * This key links BOTH transfer records together
                * and prevents accidental duplication.
            */
            $outKey = 'transfer:' . $groupId . ':out';
            $inKey ='transfer:' . $groupId . ':in';
            

            /**
                * Prepare statement for the OUTGOING transfer.
                * This represents money leaving the sender's card.
            */
            $insertOut=$pdo->prepare('INSERT INTO `transaction`
            ( Card_ID, type, Amount, from_card_id, to_card_id, initiator_type, initiator_reference, Idempotency_Key, transaction_group_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)');
            
            /**
                * Prepare statement for the INCOMING transfer.
                * This represents money entering the receiver's card.
            */
            $insertIn=$pdo->prepare('INSERT INTO `transaction`
            ( Card_ID, type, Amount, from_card_id, to_card_id, initiator_type, initiator_reference, Idempotency_Key, transaction_group_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)');
            
            /**
                * Execute OUT transaction:
                * - Card_ID = sender
                * - type = transfer_out
            */
            $insertOut->execute([$fromCardId, 'transfer_out', $amount, $fromCardId, $toCardId, 'USER', 'transfer', $outKey, $groupId ]);
            
            /**
                * Execute IN transaction:
                * - Card_ID = receiver
                * - type = transfer_in
            */
            $insertIn->execute([$toCardId, 'transfer_in', $amount, $fromCardId, $toCardId, 'USER', 'transfer',  $inKey, $groupId]);


            /**
                * Commit the transaction:
                * - Both balances are finalized
                * - Transfer is permanently recorded
            */
            $pdo->commit();

            /**
                * Return a clean API response
                * containing the transfer group ID.
            */
            return [
                    'transaction_group_id' => $groupId,
                    'amount' => $amount
                    ];

         } catch(Exception $e){
            /**
                * ERROR HANDLING
                * --------------
                * If anything fails:
                * - Roll back all changes
                * - Prevent partial transfers
                * - Return a consistent API error
            */
            if($pdo->inTransaction()){
                $pdo->rollBack();
                Json::error(500,'Transaction failed', ['details' => $e->getMessage()]);
                return [];
            }
         }
    }


}
?>