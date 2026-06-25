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

    public function allRoles(): array
    {
        $stmt = $this->db->prepare("SELECT id, role_name, description FROM roles ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

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
