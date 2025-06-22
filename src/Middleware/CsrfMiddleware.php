<?php

namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): bool {
        if($_SERVER["REQUEST_METHOD"] === "POST"){
            $token = $_POST["csrf_token"] ?? "";
            if(!$token || !hash_equals($_SESSION["csrf_token"] ?? '', $token)){
                header('Content-type: application\json');
                http_response_code(403);
                echo json_encode(['error' => "Invalid Csrf Toekn."]);
                return false;
            }
        }
        return true;
    }
}