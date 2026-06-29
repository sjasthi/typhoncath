<?php
namespace App\Core;

use PDO;

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
    public static function attempt(string $email, string $password): bool
    {
        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.password_hash, u.role_id, r.role_name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $permStmt = $db->prepare("SELECT permission FROM role_permissions WHERE role_id = ?");
        $permStmt->execute([$user['role_id']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

        // Regenerate session ID on login to prevent session fixation.
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'          => (int)$user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'role'        => $user['role_name'],
            'permissions' => $permissions,
        ];

        return true;
    }
    // clears the session and logs them out
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
