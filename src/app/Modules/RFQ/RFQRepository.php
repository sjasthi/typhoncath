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
