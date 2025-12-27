<?php
//load the variables from .env into and responses in JSON

declare(strict_types=1); // enable strict types (catches type mistakes)

$env= __DIR__.'/../.env'; // Path to the .env file

if(file_exists($env)){
    foreach(file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line){
        //Trim whitespace
        $line=trim($line);

        //skip empty sting & comments that start with #

        if($line=='' || str_starts_with($line, '#')){
            continue;
        }

        // Support lines that start with "export KEY=VALUE"
        if(strncasecmp($line, 'export',7)==0){
            $line=trim(substr($line,7));
        }

        //Split the line "KEY=VALUE" into ["KEY", "VALUE"] only
        $pair=explode('=',$line,2);

        // If the line doesn’t contain '=', ignore it safely.
        if(count($pair)!==2){
            continue;
        }

         // Clean spaces around KEY and VALUE
         [$key, $value]= array_map('trim',$pair);

          /**
         * putenv("KEY=VALUE") writes into the current PHP process environment.
         * Later we can read it anywhere via getenv('KEY').
         */
        putenv("$key=$value");

    }
}

//Default response header: we’ll send JSON from all endpoints.

header('Content-type: application/json; charset=utf-8');

//docmantion done
?>