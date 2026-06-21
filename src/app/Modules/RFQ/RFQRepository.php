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

    public function find_by_id($id){
        $db = Database::connection();
        // IMPORTANT THE '?' PREVENT SQL INJECTION AND ALLOWS FOR US TO CHECK THE
        // VALUE PASSED IN BEFORE RUNNING IT ON THE DB
        $stmt = $db->prepare("SELECT FROM rfqs WHERE id==?");
        $stmt->execute([$id]);
        return $stmt->fetch();
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
