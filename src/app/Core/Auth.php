<?php
namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        // TODO: Replace with real database lookup.
        // Seed login example: admin@typhoncath.test / password
        if ($email === 'admin@typhoncath.test' && $password === 'password') {
            $_SESSION['user'] = [
                'id' => 1,
                'name' => 'Demo Admin',
                'email' => $email,
                'role' => 'Admin',
            ];

            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
