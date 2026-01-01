<?php 
declare(strict_types=1);

namespace App;

use PDO, PDOException;

final class database{
    // Hold a single PDO connection for the whole request
    private static ?PDO $pdo=null;

    public static function pdo(): PDO{ 
        //Reuse if already created
        if(self::$pdo) return self::$pdo;
        
        //Read from environment (loaded by env.php)
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $db = getenv('DB_NAME') ?: 'payment-systemdb';
        $user = getenv('DB_USER')?: 'root';
        $pass = getenv('DB_PASS')?: '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        //DSN tells PDO how to connect to MySQL/MariaDB
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        // Sensible PDO options
        $opt=[
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION, // throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,       // get assoc arrays
            PDO::ATTR_EMULATE_PREPARES      =>false,                   // use real prepares
        ];

        try{
            self::$pdo = new PDO($dsn, $user, $pass, $opt);
            return self::$pdo;
        } catch(PDOException $e){
            /**
            * APP_ENV controls how much information we expose.
            *
            * - dev  → show detailed error (for debugging)
            * - prod → hide internals (security)
            */
            $env = getenv('APP_ENV')?: 'prod';
            if($env == 'dev'){
                Json::error(500, 'DB connection failed', ['details' => $e->getMessage()]);
            }
            // Production-safe error
            Json::error(500, 'Service temporarily unavailable');
    }
}


}
?>