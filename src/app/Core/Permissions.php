<?php
namespace App\Core;

class Permissions
{
    public static function can(string $permission): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (($user['role'] ?? '') === 'Admin' || ($user['role'] ?? '') === 'Super Admin') {
            return true;
        }

        // TODO: Load permission map from config/permissions.php.
        return false;
    }

    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
}
