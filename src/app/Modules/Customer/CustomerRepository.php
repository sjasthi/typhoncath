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
        string $source = '',
        int $limit = 25,
        int $offset = 0
    ): array {
        $db = Database::connection();

        [$where, $params] = $this->buildAccountWhere($search, $industry, $source);
        $limit  = max(1, $limit);   // guard the interpolated LIMIT/OFFSET
        $offset = max(0, $offset);

        $sql  = "SELECT * FROM accounts{$where} ORDER BY account_name LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Total accounts matching the same filters (for pagination).
    public function searchCount(string $search = '', string $industry = '', string $source = ''): int
    {
        $db = Database::connection();
        [$where, $params] = $this->buildAccountWhere($search, $industry, $source);
        $stmt = $db->prepare("SELECT COUNT(*) FROM accounts{$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Shared WHERE builder so search() and searchCount() always agree.
    private function buildAccountWhere(string $search, string $industry, string $source): array
    {
        $clauses = [];
        $params  = [];

        if ($search !== '') {
            $clauses[] = "account_name LIKE :search";
            $params['search'] = "%{$search}%";
        }
        if ($industry !== '') {
            $clauses[] = "industry LIKE :industry";
            $params['industry'] = "%{$industry}%";
        }
        if ($source !== '') {
            $clauses[] = "source LIKE :source";
            $params['source'] = "%{$source}%";
        }

        $where = $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '';
        return [$where, $params];
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
