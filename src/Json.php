<?php

declare(strict_types =1);
namespace App;

final class Json{
    //int $code  HTTP status (200, 201, etc.)
    //array $data  Your response payload
    //'never' return type means this function calls exit()
    public static function ok(int $code, array $data){
        //We send the HTTP status code that came from the function parameter
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    // int $code HTTP status (400, 401, 404, 500, …)
    //string $message Human-readable error
    public static function error(int $code, string $message, array $extra = []){
        header('Content-Type: application/json; charset=utf-8');
        //We send the HTTP status code that came from the function parameter
        http_response_code($code);
        $body=array_merge(['error' => $message],$extra);
        echo json_encode($body, JSON_UNESCAPED_SLASHES);
        exit;
    }
}
?>