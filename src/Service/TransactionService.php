<?php
declare(strict_types=1);
namespace App;

use Exception;
use PDOException;

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
        float $Amount_taken
        )
        {
            //Get a PDO connection(via Database.php)
            $pdo= database::pdo();

            try{
                

                // Generate a deterministic hash of the transaction payload.
                // This is used to enforce idempotency integrity:
                // - The same idempotency key MUST always be used with the same payload.
                // - Reusing an idempotency key with different data will produce a different hash
                //   and must be rejected to prevent duplicate or tampered transactions.

                $payloadHash = hash(
                    'sha256', 
                    
                    // Business-critical fields that uniquely define this withdrawal
                    json_encode([
                        'card_id' => $cardId,
                        'Amount'  => $Amount_taken,
                        'product' => $product,
                        'type'    => 'withdraw'

                    ],
                    // Throw an exception if JSON encoding fails to avoid hashing invalid data 
                    JSON_THROW_ON_ERROR)
                );
                //Idempotency: if this idem key was used before, return that row (no double charge)
                $check=$pdo->prepare('SELECT * FROM `transaction` WHERE `Idempotency_Key` = ? LIMIT 1');
                $check->execute([$idemkey]);
                if($existing=$check->fetch()){
                    // Compare payloads
                    // If the same idempotency key is reused, the request payload MUST be identical.
                    // A mismatch here indicates either a client bug or a potential replay/tampering attempt.
                    if($existing['payload_hash'] !== $payloadHash){
                        // Roll back any open transaction to ensure no partial state changes
                        if($pdo->inTransaction()){
                            $pdo->rollBack();
                        }
                        // Reject the request to prevent conflicting or duplicate transactions
                        // HTTP 409 Conflict is returned because the idempotency key
                        Json::error(409,'Idempotency key already used with a different payload');
                    }
                    
                    // Return the previously recorded transaction instead of executing
                    return [
                        'idempotent'  => true,
                        'transaction' => $existing,
                        'note'        => 'Same Idempotency_Key used; returning previous result.'
                    ];
                }
                // Start atomic transaction (everything succeeds or nothing
                // telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
                $pdo->beginTransaction();
                // Serch for the card_id if not found return 404 status code
                $card_check =$pdo->prepare('SELECT * FROM card WHERE Card_ID =? FOR UPDATE');
                $card_check->execute([$cardId]);
                $card = $card_check->fetch();
                if(!$card){
                    Json::error(404, 'Card not found');
                }

                //Insert a transaction row; triggers will set Balance_After + status and update card if success                
                $insert=$pdo->prepare(' INSERT INTO `transaction`
                (`Card_ID`, `Product`, `Amount`, `type`,`Idempotency_Key`, `payload_hash`)
                VALUES (?, ?, ?, ?, ?,?)');
                $insert->execute([$cardId, $product,$Amount_taken,'withdraw', $idemkey, $payloadHash]);
                
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
                // DEBUG: Log the actual error
                    error_log("===== ACTUAL ERROR =====");
                    error_log("Message: " . $e->getMessage());
                    error_log("Code: " . $e->getCode());
                    error_log("File: " . $e->getFile() . ":" . $e->getLine());

                    if($e instanceof PDOException){
                        error_log("PDO Error Info: " . json_encode($e->errorInfo));
                        error_log("SQLSTATE: " . $e->errorInfo[0]);
                        error_log("Driver Code: " . $e->errorInfo[1]);
                        error_log("Driver Message: " . $e->errorInfo[2]);
                    }

                    error_log("Stack Trace: " . $e->getTraceAsString());
                    error_log("===== END ERROR =====");
    
                // check if there active database transaction right now
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                }
                // Temporarily return the actual error for debugging
                Json::error(500, "Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
                
                /*
                 if ($e->getCode() === '45000') {
                  Json::error(422, $e->getMessage());
                }
                // If the exception message indicates an insufficient balance,
                // return a client error (422) because the request itself is valid,
                // but cannot be processed due to business rules (not enough funds).
                if(str_contains($e->getMessage(), 'Insufficient funds')){
                    Json::error(422, 'Insufficient funds');
                }
                // Any other exception is treated as an internal system failure.
                // This prevents leaking sensitive error details (DB errors, stack traces, etc)
                // and keeps the API consistent and secure.
                Json::error(500, 'Transaction failed'); 
                */
                
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

                // Generate a deterministic hash of the transaction payload.
                // This is used to enforce idempotency integrity:
                // - The same idempotency key MUST always be used with the same payload.
                // - Reusing an idempotency key with different data will produce a different hash
                //   and must be rejected to prevent duplicate or tampered transactions.

                $payloadHash = hash(
                    'sha256',

                    // Business-critical fields that uniquely define this withdrawal
                    json_encode([
                        'card_id' => $cardId,
                        'Amount'  => $Amount_send,
                        'product' => $product,
                        'type'    => 'deposit'
                    ],
                    // Throw an exception if JSON encoding fails to avoid hashing invalid data
                    JSON_THROW_ON_ERROR)
                );

                //idempotency: if this idem key was used before, return that raw (no double charge)
                $check=$pdo->prepare('SELECT * FROM `transaction` WHERE `Idempotency_Key` = ? LIMIT 1');
                $check->execute([$idemkey]);
                if($existing=$check->fetch()){
                    // Compare payloads
                    // If the same idempotency key is reused, the request payload MUST be identical.
                    // A mismatch here indicates either a client bug or a potential replay/tampering attempt.
                    if($existing['payload_hash'] !== $payloadHash){
                        // Roll back any open transaction to ensure no partial state changes
                        if($pdo->inTransaction()){
                            $pdo->rollBack();
                        }
                        // Reject the request to prevent conflicting or duplicate transactions
                        // HTTP 409 Conflict is returned because the idempotency key
                        Json::error(409, 'Idempotency key already used with a different payload');

                    }
                    // Return the previously recorded transaction instead of executing
                    return[
                        'idempotent'  => true,
                        'transaction' => $existing,
                        'note'        => 'Same Idempotency_Key used; returning previous result.'
                    ];
                }
                // Start atomic transaction (everything succeeds or nothing
                // telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
                $pdo->beginTransaction();
                // Serch for the card_id if not found return 404 status code
                $card_check =$pdo->prepare('SELECT FROM card WHERE Card_ID =? FOR UPDATE');
                $card_check->execute([$cardId]);
                $card = $card_check->fetch();
                if(!$card){
                    Json::error(404, 'Card not found');
                }
                
                $insert=$pdo->prepare(' INSERT INTO `transaction`
                (`Card_ID`, `Product`, `Amount`, `type`,`Idempotency_Key`, `payload_hash`)
                VALUES (?, ?, ?, ?,?, ?)');
                $insert->execute([$cardId, $product,$Amount_send,'deposit', $idemkey, $payloadHash]);

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
                }
                if ($e->getCode() === '45000') {
                    Json::error(422, $e->getMessage());
                }
                // If the exception message indicates an insufficient balance,
                // return a client error (422) because the request itself is valid,
                // but cannot be processed due to business rules (not enough funds).
                if(str_contains($e->getMessage(), "Insufficient funds")){
                    return Json::error(422, 'Insufficient funds');
                }
                // Any other exception is treated as an internal system failure.
                // This prevents leaking sensitive error details (DB errors, stack traces, etc)
                // and keeps the API consistent and secure.
                Json::error(500, 'Transaction failed');
                
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
            ( Card_ID, type, Amount, from_card_id, to_card_id, initiator_type, initiator_id, initiator_reference, Idempotency_Key, transaction_group_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)');
            
            /**
                * Prepare statement for the INCOMING transfer.
                * This represents money entering the receiver's card.
            */
            $insertIn=$pdo->prepare('INSERT INTO `transaction`
            ( Card_ID, type, Amount, from_card_id, to_card_id, initiator_type, initiator_id, initiator_reference, Idempotency_Key, transaction_group_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)');
            
            /**
                * Execute OUT transaction:
                * - Card_ID = sender
                * - type = transfer_out
            */
            $insertOut->execute([$fromCardId, 'transfer_out', $amount, $fromCardId, $toCardId, 'User', $fromCardId, 'transfer', $outKey, $groupId ]);
            
            /**
                * Execute IN transaction:
                * - Card_ID = receiver
                * - type = transfer_in
            */
            $insertIn->execute([$toCardId, 'transfer_in', $amount, $fromCardId, $toCardId, 'User', $fromCardId, 'transfer',  $inKey, $groupId]);


            /**
                * Commit the transaction:
                * - Both balances are finalized
                * - Transfer is permanently recorded
            */
            $pdo->commit();
            
            // Fetch the creation timestamp for the transaction group.
            // LIMIT 1 is used since the group ID uniquely identifies the record.
            $stmt = $pdo->prepare(
                'SELECT Created_At FROM `transaction`
                WHERE transaction_group_id = ?
                LIMIT 1');

            // Execute the query with the provided transaction group identifier
            $stmt->execute([$groupId]);
            // Retrieve the Created_At value 
            $createdAt = $stmt->fetchColumn();

            /**
                * Return a clean API response
                * containing the transfer group ID.
            */
            return [
                    'transaction_group_id' => $groupId,
                    'amount'               => $amount,
                    'created_at'           => $createdAt,
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
            }
            // If the exception message indicates an insufficient balance,
            // return a client error (422) because the request itself is valid,
            // but cannot be processed due to business rules (not enough funds).
            if(str_contains($e->getMessage(), 'Insufficient funds')){
                Json::error(422, 'Insufficient funds');
            }
         }
    }


}
?>