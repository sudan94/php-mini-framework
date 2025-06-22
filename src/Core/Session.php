<?php

namespace App\Core;

class Session
{
    // Lifetime of session in seconds (e.g., 30 minutes)
    private const TIMEOUT = 1800;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }

        // Check for timeout
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > self::TIMEOUT)) {
            self::destroy();
            self::start(); // restart clean session
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function flash(string $key)
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function isAuthenticated(): bool
    {
        return self::has('user_id');
    }

    public static function login(int $userId, string $email): void
    {
        self::regenerate();
        self::set('user_id', $userId);
        self::set('user_email', $email);

    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
