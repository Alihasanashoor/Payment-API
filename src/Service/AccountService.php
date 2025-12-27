<?php
declare(strict_types=1);
namespace App;

use Exception;

final class Account{
    public static function CreateAccount(
        string $Name,
        string $Phone_Number,
        string $emali,
        ?int $linkId,
        float $balance
    ){
        //Get a PDO connection(via Database.php)
        $pdo= database::pdo();

        try{
            //Start atomic transaction (everything succeeds or nothing
            // telling MySQL: “I’m starting a transaction — don’t finalize changes until I say so.”
            $pdo->beginTransaction();
            
            // create account, insert in the database
            $insert= $pdo->prepare('INSERT INTO `accounts`
            (`Name`, `Phone_Number`, `email`, `Link_ID`)
            VALUES (?,?,?,?)');
            $insert->execute([$Name, $Phone_Number, $emali, $linkId]);

            // Return the auto-increment Account_ID
            $AccountID = (int) $pdo->lastInsertId();

            // create card for the new user
            $create_card= $pdo->prepare('INSERT INTO `card`
            (`Account_ID`, `Balance`)
            VALUES (?,?)');
            $create_card->execute([$AccountID, $balance]);
            // Return the auto-increment Card_ID
            $card_id= (int) $pdo->lastInsertId();

            // Query both account & card row back

            // Query account row
            $get= $pdo->prepare('SELECT * FROM `accounts` WHERE `Account_ID` = ?');
            $get->execute([$AccountID]);
            $row=$get->fetch();
            

            // Query card row
            $get_card= $pdo->prepare('SELECT * FROM `card` WHERE `Card_ID` = ?');
            $get_card->execute([$card_id]);
            $card_row= $get_card->fetch();

            $pdo->commit();

            return[
                // account information
                'account'=>[
                    'Name'           =>    $row['Name'],
                    'Phone_Number'   =>    $row['Phone_Number'],
                    'emali'          =>    $row['email'],
                    'Link_ID'        =>    $row['Link_ID']  
                ]

                ,
                // card information
                'card' =>[
                    'card_number'    =>    $card_row['card_number'],
                    'Balance'        =>    $card_row['Balance'],
                ]
            ];
            

        
        } catch(Exception $e){
            // check if there active database transaction right now
            if($pdo->inTransaction()){
                $pdo->rollBack();
            }
            Json::error(500, 'failed to create account' , ['detalis' => $e->getMessage()]);
            return [];
            
        }

    }
}





?>