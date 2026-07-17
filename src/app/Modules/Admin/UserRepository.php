<?php
namespace App\Modules\Admin;

use App\Core\Database;
use App\Core\DataTable\ServerTable;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Server-side DataTables source for the Admin users list. Shared by the data
     * and export endpoints.
     */
    public static function listTable(): ServerTable
    {
        return new ServerTable(
            Database::connection(),
            'users u JOIN roles r ON r.id = u.role_id',
            'u.id, u.name, u.email, u.role_id, r.role_name, r.owner_user_id, u.created_at',
            [
                ['data' => 'name',       'sql' => 'u.name',       'order' => true,  'search' => 'like'],
                ['data' => 'email',      'sql' => 'u.email',      'order' => true,  'search' => 'like'],
                ['data' => 'role',       'sql' => 'r.role_name',  'order' => true,  'search' => 'like'],
                ['data' => 'created_at', 'sql' => 'u.created_at', 'order' => true,  'search' => false],
                ['data' => 'actions',    'sql' => '',             'order' => false, 'search' => false],
            ],
            'u.name',
            'ASC'
        );
    }

    public function allUsers(?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT u.id, u.name, u.email, u.role_id, r.role_name, r.owner_user_id, u.created_at
                FROM users u
                JOIN roles r ON r.id = u.role_id
                ORDER BY u.name ASC";

        if ($limit !== null) {
            $limit  = max(1, $limit);   // guard the interpolated LIMIT/OFFSET
            $offset = max(0, $offset);
            $sql   .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Total user count (for pagination).
    public function countUsers(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.role_id, r.role_name, r.owner_user_id, u.created_at, u.updated_at
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?"
        );
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash, role_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password_hash'],
            (int)$data['role_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE users SET name = ?, email = ?, role_id = ? WHERE id = ?
        ");
        $stmt->execute([$data['name'], $data['email'], (int)$data['role_id'], $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                 ->execute([$hash, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
}
