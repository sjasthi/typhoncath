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

    public function search(
        string $search = '',
        string $industry = '',
        string $source = ''
    ): array {

        $db = Database::connection();

        $sql = "
            SELECT *
            FROM accounts
            WHERE 1=1
        ";

        $params = [];

        if ($search !== '') {
            $sql .= " AND account_name LIKE :search";
            $params['search'] = "%{$search}%";
        }

        if ($industry !== '') {
            $sql .= " AND industry LIKE :industry";
            $params['industry'] = "%{$industry}%";
        }

        if ($source !== '') {
            $sql .= " AND source LIKE :source";
            $params['source'] = "%{$source}%";
        }

        $sql .= " ORDER BY account_name";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

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

    // A contact must belong to an account (contacts.account_id is NOT NULL).
    public function createContact(array $data): void
    {
        $db = Database::connection();

        $stmt = $db->prepare("
            INSERT INTO contacts
            (
                account_id,
                first_name,
                last_name,
                email,
                phone,
                title
            )
            VALUES
            (
                :account_id,
                :first_name,
                :last_name,
                :email,
                :phone,
                :title
            )
        ");

        $stmt->execute($data);
    }
}
