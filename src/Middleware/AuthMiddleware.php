<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        $currentPath = $_SERVER['REQUEST_URI'];
        $isLoggedIn = isset($_SESSION['user_id']);

        // If logged in and trying to access login/register, redirect to home
        if ($isLoggedIn && ($currentPath === '/login' || $currentPath === '/register')) {
            header('Location: /');
            return false;
        }

        // If not logged in and trying to access protected routes, redirect to login
        if (!$isLoggedIn && $currentPath !== '/login' && $currentPath !== '/register') {
            header('Location: /login');
            return false;
        }

        return true;
    }
}