<?php
namespace App\Modules\RFQ;

use App\Core\Database;

class RFQRepository
{

    public function all(): array{   
        // TODO: Replace with module-specific prepared queries.
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM rfqs ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public static array $stages = ['New', 'In Review', 'Quoted', 'Negotiation', 'Won', 'Lost'];

    private function buildWhere(string $q, string $idSearch, array $stages): array {
        $clauses = [];
        $params  = [];

        if ($idSearch !== '') {
            $clauses[] = 'r.id = ?';
            $params[]  = (int)$idSearch;
        }
        if ($q !== '') {
            $clauses[] = '(r.title LIKE ? OR a.account_name LIKE ?)';
            $params[]  = "%$q%";
            $params[]  = "%$q%";
        }
        $validStages = array_values(array_intersect($stages, self::$stages));
        if (!empty($validStages)) {
            $placeholders = implode(',', array_fill(0, count($validStages), '?'));
            $clauses[]    = "r.stage IN ($placeholders)";
            array_push($params, ...$validStages);
        }

        $sql = $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '';
        return [$sql, $params];
    }

    public function searchCount(string $q = '', string $idSearch = '', array $stages = []): int {
        [$where, $params] = $this->buildWhere($q, $idSearch, $stages);
        $db   = Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM rfqs r LEFT JOIN accounts a ON a.id = r.account_id" . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function search(string $q = '', string $sortCol = 'created_at', string $sortDir = 'DESC', int $limit = 25, int $offset = 0, string $idSearch = '', array $stages = []): array {
        $colMap = [
            'id'           => 'r.id',
            'title'        => 'r.title',
            'account_name' => 'a.account_name',
            'stage'        => 'r.stage',
            'created_at'   => 'r.created_at',
            'updated_at'   => 'r.updated_at',
        ];
        $orderCol = $colMap[$sortCol] ?? 'r.created_at';
        $orderDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        [$where, $params] = $this->buildWhere($q, $idSearch, $stages);

        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT r.id, r.title, a.account_name, r.stage, r.created_at, r.updated_at
            FROM rfqs r
            LEFT JOIN accounts a ON a.id = r.account_id
            $where
            ORDER BY $orderCol $orderDir LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allAccounts(): array {
        $db   = Database::connection();
        $stmt = $db->prepare("SELECT id, account_name FROM accounts ORDER BY account_name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function allContacts(): array {
        $db   = Database::connection();
        $stmt = $db->prepare("SELECT id, account_id, first_name, last_name, title FROM contacts ORDER BY last_name ASC, first_name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $db   = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO rfqs (account_id, contact_id, created_by_user_id, title, description, stage)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['account_id'],
            $data['contact_id'] !== null ? (int)$data['contact_id'] : null,
            (int)$data['created_by_user_id'],
            $data['title'],
            $data['description'],
            $data['stage'],
        ]);
        return (int)$db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT
                r.id, r.title, r.description, r.stage, r.created_at, r.updated_at,
                a.id            AS account_id,
                a.account_name, a.email AS account_email, a.phone AS account_phone,
                c.id            AS contact_id,
                CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                c.email         AS contact_email,
                c.phone         AS contact_phone,
                c.title         AS contact_title,
                u.name          AS created_by_name
            FROM rfqs r
            LEFT JOIN accounts a ON a.id = r.account_id
            LEFT JOIN contacts c ON c.id = r.contact_id
            LEFT JOIN users   u ON u.id = r.created_by_user_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getQuotesByRfqId(int $id): array {
        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT
                id, quote_amount, discount, validity_start_date, validity_end_date, created_at,
                DATEDIFF(validity_end_date, CURDATE()) AS days_remaining
            FROM quotes
            WHERE rfq_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getReservationsByRfqId(int $id): array {
        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT
                res.id, res.quantity_reserved, res.reservation_status, res.created_at,
                p.product_name, p.sku, p.price
            FROM rfq_inventory_reservations res
            JOIN products p ON p.id = res.product_id
            WHERE res.rfq_id = ?
            ORDER BY res.created_at DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function allProducts(): array {
        $db   = Database::connection();
        $stmt = $db->prepare("
            SELECT p.id, p.product_name, p.sku, p.price, p.description,
                   COALESCE(i.available_quantity, 0) AS available_quantity
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            ORDER BY p.product_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function insertReservation(int $rfqId, array $data): int {
        $db  = Database::connection();
        $qty = (int)$data['quantity_reserved'];

        $stmt = $db->prepare("
            INSERT INTO rfq_inventory_reservations (rfq_id, product_id, quantity_reserved, reservation_status)
            VALUES (?, ?, ?, 'Reserved')
        ");
        $stmt->execute([$rfqId, (int)$data['product_id'], $qty]);
        $reservationId = (int)$db->lastInsertId();

        // Keep inventory counts in sync
        $db->prepare("
            UPDATE inventory
            SET available_quantity = available_quantity - ?,
                reserved_quantity  = reserved_quantity  + ?
            WHERE product_id = ?
        ")->execute([$qty, $qty, (int)$data['product_id']]);

        return $reservationId;
    }

    public function insertQuote(int $rfqId, array $data): int {
        $db   = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO quotes (rfq_id, quote_amount, discount, validity_start_date, validity_end_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $rfqId,
            (float)$data['quote_amount'],
            isset($data['discount']) && $data['discount'] !== '' ? (float)$data['discount'] : 0,
            $data['validity_start_date'] !== '' ? $data['validity_start_date'] : null,
            $data['validity_end_date']   !== '' ? $data['validity_end_date']   : null,
        ]);
        return (int)$db->lastInsertId();
    }

    public function updateStage(int $id, string $stage): void {
        $db   = Database::connection();
        $stmt = $db->prepare("UPDATE rfqs SET stage = ? WHERE id = ?");
        $stmt->execute([$stage, $id]);
    }

    public function update(int $id, array $data): void {
        $db   = Database::connection();
        $stmt = $db->prepare("
            UPDATE rfqs
            SET title = ?, account_id = ?, contact_id = ?, description = ?, stage = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'],
            (int)$data['account_id'],
            $data['contact_id'] !== null ? (int)$data['contact_id'] : null,
            $data['description'],
            $data['stage'],
            $id,
        ]);
    }

    public function find_by_id($id){
        return $this->findById((int)$id);
    }

    public function winRateByAccount(): array {
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT
                a.account_name,
                COUNT(r.id)                                          AS total_rfqs,
                SUM(r.stage = 'Won')                                 AS won,
                SUM(r.stage = 'Lost')                                AS lost,
                ROUND(
                    SUM(r.stage = 'Won')
                    / NULLIF(SUM(r.stage IN ('Won','Lost')), 0) * 100
                , 1)                                                 AS win_rate_pct
            FROM rfqs r
            JOIN accounts a ON a.id = r.account_id
            GROUP BY a.id, a.account_name
            ORDER BY win_rate_pct DESC, total_rfqs DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalValueByStage(): array {
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT
                r.stage,
                COUNT(r.id)                        AS rfq_count,
                COALESCE(SUM(q.quote_amount), 0)   AS total_value,
                COALESCE(AVG(q.quote_amount), 0)   AS avg_value
            FROM rfqs r
            LEFT JOIN quotes q ON q.rfq_id = r.id
            GROUP BY r.stage
            ORDER BY FIELD(r.stage, 'New', 'In Review', 'Quoted', 'Negotiation', 'Won', 'Lost')
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function quotesExpiringSoon(): array {
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT
                r.id,
                r.title,
                r.stage,
                a.account_name,
                q.quote_amount,
                q.validity_end_date,
                DATEDIFF(q.validity_end_date, CURDATE()) AS days_remaining
            FROM rfqs r
            JOIN quotes q  ON q.rfq_id  = r.id
            JOIN accounts a ON a.id     = r.account_id
            WHERE r.stage IN ('Quoted', 'Negotiation')
            ORDER BY q.validity_end_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
