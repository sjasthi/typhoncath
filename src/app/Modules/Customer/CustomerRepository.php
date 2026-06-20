<?php
namespace App\Modules\Customer;

use App\Core\Database;
use PDO;

class CustomerRepository
{
    public function all(): array
    {
        $db = Database::connection();

        $stmt = $db->query("
            SELECT *
            FROM accounts
            ORDER BY account_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        $db = Database::connection();

        $stmt = $db->prepare("
            INSERT INTO accounts
            (
                account_name,
                email,
                phone,
                address,
                industry,
                source,
                tags
            )
            VALUES
            (
                :account_name,
                :email,
                :phone,
                :address,
                :industry,
                :source,
                :tags
            )
        ");

        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $db = Database::connection();

        $stmt = $db->prepare("
            DELETE FROM accounts
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id
        ]);
    }
}
