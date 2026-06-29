<?php
namespace App\Modules\Admin;

use App\Core\Database;
use PDO;

class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // ── Roles ─────────────────────────────────────────────────────────────────

    public function allRoles(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, role_name, description, owner_user_id FROM roles ORDER BY id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Only the 5 system roles — used to populate dropdowns.
    public function allStandardRoles(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, role_name FROM roles WHERE owner_user_id IS NULL ORDER BY id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findRoleById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, role_name, description, owner_user_id FROM roles WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insertRole(string $name, string $description, int $ownerUserId): int
    {
        $this->db->prepare(
            "INSERT INTO roles (role_name, description, owner_user_id) VALUES (?, ?, ?)"
        )->execute([$name, $description, $ownerUserId]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteRole(int $id): void
    {
        // Only delete custom roles — guard prevents deleting system roles.
        $this->db->prepare(
            "DELETE FROM roles WHERE id = ? AND owner_user_id IS NOT NULL"
        )->execute([$id]);
    }

    // Deletes any custom role owned by $userId that no user is currently assigned to.
    // Cleans up orphans left by a failed previous custom-role creation attempt.
    public function deleteOrphanedCustomRoleForUser(int $userId): void
    {
        $this->db->prepare(
            "DELETE FROM roles
             WHERE owner_user_id = ?
               AND id NOT IN (SELECT u.role_id FROM users u)"
        )->execute([$userId]);
    }

    // ── Permissions ───────────────────────────────────────────────────────────

    // Returns a lookup set keyed as "role_id:permission" for O(1) matrix rendering.
    public function rolePermissionsMap(): array
    {
        $stmt = $this->db->prepare("SELECT role_id, permission FROM role_permissions");
        $stmt->execute();
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['role_id'] . ':' . $row['permission']] = true;
        }
        return $map;
    }

    // Replaces all non-Super-Admin permissions atomically.
    // Super Admin (role_id=1) uses a wildcard in Permissions::can() — not stored here.
    public function savePermissions(array $matrix): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM role_permissions WHERE role_id != 1")->execute();

            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)"
            );
            foreach ($matrix as $roleId => $permissions) {
                $roleId = (int)$roleId;
                if ($roleId <= 1) continue;
                foreach ((array)$permissions as $perm) {
                    if (trim($perm) !== '') {
                        $stmt->execute([$roleId, trim($perm)]);
                    }
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
