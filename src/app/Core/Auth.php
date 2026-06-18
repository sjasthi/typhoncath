<?php
namespace App\Core;

class Auth
{
    // Checks whether someone is logged in.
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }
    // returns current user
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
    // preforms a user check to verify credentails 
    // if user is not logged in it sends them to /login

    // not the auth function, just a basic check
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }
    // attempts to login, we need real auth which looks like 
    // user submits -> db call -> looks up user table and then returns boolean

    // password_verify($password, $user['password_hash'])
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
    // clears the session and logs them out
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
