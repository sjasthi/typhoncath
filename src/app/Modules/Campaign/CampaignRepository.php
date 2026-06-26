<?php
namespace App\Modules\Campaign;

use App\Core\Database;
use PDO;

class CampaignRepository
{
    public static array $statuses = ['Draft', 'Scheduled', 'Sent', 'Completed'];
    public static array $types    = ['Email', 'SMS Simulation'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // ── List / Search ──────────────────────────────────────────────────────────
    // status filter hits idx_campaigns_status; created_at sort hits idx_campaigns_created_at

    public function searchCount(string $q = '', array $statuses = []): int
    {
        [$where, $params] = $this->buildWhere($q, $statuses);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM campaigns c" . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function search(
        string $q        = '',
        string $sortCol  = 'created_at',
        string $sortDir  = 'DESC',
        int    $limit    = 25,
        int    $offset   = 0,
        array  $statuses = []
    ): array {
        $colMap = [
            'id'            => 'c.id',
            'campaign_name' => 'c.campaign_name',
            'campaign_type' => 'c.campaign_type',
            'status'        => 'c.status',
            'sent_count'    => 'c.sent_count',
            'created_at'    => 'c.created_at',
            'updated_at'    => 'c.updated_at',
        ];
        $orderCol = $colMap[$sortCol] ?? 'c.created_at';
        $orderDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        [$where, $params] = $this->buildWhere($q, $statuses);

        $stmt = $this->db->prepare("
            SELECT c.id, c.campaign_name, c.campaign_type, c.status,
                   c.sent_count, c.open_rate, c.click_rate, c.created_at
            FROM campaigns c
            {$where}
            ORDER BY {$orderCol} {$orderDir}
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Lookups ────────────────────────────────────────────────────────────────
    // JOIN to users hits users.id (PK) — fast. campaigns.created_by_user_id covered by idx_campaigns_created_by_user_id.

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.campaign_name, c.campaign_type, c.status, c.scheduled_at,
                   c.sent_count, c.open_rate, c.click_rate,
                   c.created_at, c.updated_at,
                   u.name AS created_by_name
            FROM campaigns c
            LEFT JOIN users u ON u.id = c.created_by_user_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allAccounts(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, account_name, tags FROM accounts ORDER BY account_name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function allContacts(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.first_name, c.last_name, c.account_id, a.account_name
            FROM contacts c
            JOIN accounts a ON a.id = c.account_id
            ORDER BY c.last_name ASC, c.first_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Audience ───────────────────────────────────────────────────────────────
    // campaign_id lookup hits idx_campaign_audience_campaign_id.
    // JOINs to accounts/contacts use their PKs and FK indexes.

    // Returns raw rows including account_id / contact_id for grouping logic.
    public function getAudienceByCampaignId(int $campaignId): array
    {
        $stmt = $this->db->prepare("
            SELECT ca.id, ca.segment_name, ca.tag_filter,
                   ca.account_id, ca.contact_id,
                   a.account_name,
                   CONCAT(c.first_name, ' ', c.last_name) AS contact_name
            FROM campaign_audience ca
            LEFT JOIN accounts a ON a.id = ca.account_id
            LEFT JOIN contacts c ON c.id = ca.contact_id
            WHERE ca.campaign_id = ?
            ORDER BY ca.segment_name ASC, ca.id ASC
        ");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    // Returns all raw rows for a named segment — used to pre-fill the edit form.
    public function getAudienceSegment(int $campaignId, string $segmentName): array
    {
        $stmt = $this->db->prepare("
            SELECT id, account_id, contact_id, tag_filter, segment_name
            FROM campaign_audience
            WHERE campaign_id = ? AND segment_name = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$campaignId, $segmentName]);
        return $stmt->fetchAll();
    }

    public function insertAudience(int $campaignId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaign_audience (campaign_id, account_id, contact_id, tag_filter, segment_name)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $campaignId,
            !empty($data['account_id']) ? (int)$data['account_id'] : null,
            !empty($data['contact_id']) ? (int)$data['contact_id'] : null,
            ($data['tag_filter']   ?? '') !== '' ? $data['tag_filter']   : null,
            ($data['segment_name'] ?? '') !== '' ? $data['segment_name'] : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Removes all rows belonging to a named segment within a campaign.
    public function deleteAudienceBySegment(int $campaignId, string $segmentName): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM campaign_audience WHERE campaign_id = ? AND segment_name = ?"
        );
        $stmt->execute([$campaignId, $segmentName]);
    }

    // ── Audience Presets ───────────────────────────────────────────────────────

    public function allPresets(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, preset_name, segment_name, tag_filter, account_ids, contact_ids
             FROM audience_presets ORDER BY preset_name ASC"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();
        // Decode JSON columns so callers get plain arrays.
        foreach ($rows as &$row) {
            $row['account_ids'] = json_decode($row['account_ids'] ?? '[]', true) ?: [];
            $row['contact_ids'] = json_decode($row['contact_ids'] ?? '[]', true) ?: [];
        }
        return $rows;
    }

    public function findPresetById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, preset_name, segment_name, tag_filter, account_ids, contact_ids
             FROM audience_presets WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['account_ids'] = json_decode($row['account_ids'] ?? '[]', true) ?: [];
        $row['contact_ids'] = json_decode($row['contact_ids'] ?? '[]', true) ?: [];
        return $row;
    }

    public function insertPreset(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO audience_presets
                (preset_name, segment_name, tag_filter, account_ids, contact_ids, created_by_user_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['preset_name'],
            $data['segment_name'],
            ($data['tag_filter'] ?? '') !== '' ? $data['tag_filter'] : null,
            json_encode(array_values(array_filter(array_map('intval', $data['account_ids'] ?? [])))),
            json_encode(array_values(array_filter(array_map('intval', $data['contact_ids'] ?? [])))),
            (int)$data['created_by_user_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Counts the recipients already saved for a campaign.
    // Explicit account/contact IDs are counted directly; tag-filter-only rows trigger
    // a FIND_IN_SET account lookup so the total reflects real DB matches.
    public function countSavedAudience(int $campaignId): array
    {
        $rows = $this->db->prepare("
            SELECT account_id, contact_id, tag_filter
            FROM campaign_audience
            WHERE campaign_id = ?
        ");
        $rows->execute([$campaignId]);
        $entries = $rows->fetchAll();

        $accountIds = [];
        $contactIds = [];
        $tagFilters = [];

        foreach ($entries as $e) {
            if ($e['account_id'] !== null) $accountIds[] = (int)$e['account_id'];
            if ($e['contact_id'] !== null) $contactIds[] = (int)$e['contact_id'];
            if ($e['tag_filter']  !== null) $tagFilters[] = $e['tag_filter'];
        }

        // Reuse previewAudienceCount: merge all tag filters into one string,
        // pass all explicit IDs — it handles deduplication via COUNT(DISTINCT).
        $combinedTag = implode(',', $tagFilters);
        return $this->previewAudienceCount($combinedTag, array_unique($accountIds), array_unique($contactIds));
    }

    // Returns ['accounts' => int, 'contacts' => int] for live preview.
    // Account query uses FIND_IN_SET on the tags column — acceptable at small scale.
    // Contact query hits contacts.id (PK) directly.
    public function previewAudienceCount(string $tagFilter, array $accountIds, array $contactIds): array
    {
        $accountCount = 0;
        $contactCount = 0;

        $aClauses = [];
        $aParams  = [];
        if (!empty($accountIds)) {
            $ph = implode(',', array_fill(0, count($accountIds), '?'));
            $aClauses[] = "id IN ({$ph})";
            array_push($aParams, ...$accountIds);
        }
        foreach (array_filter(array_map('trim', explode(',', $tagFilter))) as $tag) {
            $aClauses[] = 'FIND_IN_SET(?, tags)';
            $aParams[]  = $tag;
        }
        if ($aClauses) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(DISTINCT id) FROM accounts WHERE " . implode(' OR ', $aClauses)
            );
            $stmt->execute($aParams);
            $accountCount = (int)$stmt->fetchColumn();
        }

        if (!empty($contactIds)) {
            $ph = implode(',', array_fill(0, count($contactIds), '?'));
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contacts WHERE id IN ({$ph})");
            $stmt->execute($contactIds);
            $contactCount = (int)$stmt->fetchColumn();
        }

        return ['accounts' => $accountCount, 'contacts' => $contactCount];
    }

    // ── Writes ─────────────────────────────────────────────────────────────────

    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO campaigns (campaign_name, campaign_type, status, scheduled_at, created_by_user_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['campaign_name'],
            $data['campaign_type'],
            $data['status'],
            $data['scheduled_at'] ?? null,
            (int)$data['created_by_user_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE campaigns SET campaign_name = ?, campaign_type = ?, status = ?, scheduled_at = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['campaign_name'],
            $data['campaign_type'],
            $data['status'],
            $data['scheduled_at'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Sets sent_count, open_rate, click_rate and flips status to Sent atomically.
    public function updateMetrics(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE campaigns
            SET sent_count = ?, open_rate = ?, click_rate = ?, status = 'Sent'
            WHERE id = ?
        ");
        $stmt->execute([
            (int)$data['sent_count'],
            $data['open_rate']  !== null ? (float)$data['open_rate']  : null,
            $data['click_rate'] !== null ? (float)$data['click_rate'] : null,
            $id,
        ]);
    }

    // ── Dashboard Queries ──────────────────────────────────────────────────────
    // idx_campaigns_status_scheduled_at covers (status, scheduled_at) for WHERE + ORDER BY.

    public function upcomingScheduledSends(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.campaign_name, c.campaign_type, c.scheduled_at,
                   DATEDIFF(c.scheduled_at, NOW()) AS days_until,
                   u.name AS created_by_name
            FROM campaigns c
            LEFT JOIN users u ON u.id = c.created_by_user_id
            WHERE c.status = 'Scheduled' AND c.scheduled_at >= NOW()
            ORDER BY c.scheduled_at ASC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Top-performing Sent/Completed campaigns ordered by open_rate DESC.
    // idx_campaigns_status_open_rate covers (status, open_rate, sent_count).
    public function topPerformers(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT id, campaign_name, campaign_type, status,
                   sent_count, open_rate, click_rate, scheduled_at
            FROM campaigns
            WHERE status IN ('Sent', 'Completed') AND open_rate IS NOT NULL
            ORDER BY open_rate DESC, sent_count DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Contacts/accounts that appear in sent campaigns with 0 click-through — cold list.
    // Grouped by recipient; ordered by most zero-click campaigns first.
    public function reEngagementCandidates(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT
                CASE WHEN ca.contact_id IS NOT NULL
                     THEN CONCAT(con.first_name, ' ', con.last_name)
                     ELSE a.account_name
                END AS recipient_name,
                CASE WHEN ca.contact_id IS NOT NULL THEN 'Contact' ELSE 'Account' END AS recipient_type,
                COUNT(DISTINCT ca.campaign_id) AS campaigns_targeted,
                SUM(CASE WHEN c.click_rate IS NULL OR c.click_rate = 0 THEN 1 ELSE 0 END) AS zero_click_campaigns,
                ROUND(AVG(c.open_rate), 1) AS avg_open_rate,
                DATE(MAX(c.created_at)) AS last_targeted_at
            FROM campaign_audience ca
            JOIN campaigns c ON c.id = ca.campaign_id
                AND c.status IN ('Sent', 'Completed')
            LEFT JOIN contacts con ON con.id = ca.contact_id
            LEFT JOIN accounts a   ON a.id   = ca.account_id
            WHERE ca.contact_id IS NOT NULL OR ca.account_id IS NOT NULL
            GROUP BY ca.contact_id, ca.account_id
            HAVING SUM(CASE WHEN c.click_rate IS NULL OR c.click_rate = 0 THEN 1 ELSE 0 END) > 0
            ORDER BY zero_click_campaigns DESC, campaigns_targeted DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Weekly campaign activity for the momentum chart — last N weeks.
    public function campaignMomentum(int $weeks = 12): array
    {
        $stmt = $this->db->prepare("
            SELECT
                YEARWEEK(created_at, 1) AS week_key,
                DATE_FORMAT(MIN(created_at), '%b %d') AS week_label,
                COUNT(*) AS campaigns_created,
                SUM(status IN ('Sent', 'Completed')) AS campaigns_sent,
                COALESCE(SUM(CASE WHEN status IN ('Sent','Completed') THEN sent_count ELSE 0 END), 0) AS total_recipients,
                ROUND(AVG(CASE WHEN status IN ('Sent','Completed') AND open_rate  IS NOT NULL THEN open_rate  END), 1) AS avg_open_rate,
                ROUND(AVG(CASE WHEN status IN ('Sent','Completed') AND click_rate IS NOT NULL THEN click_rate END), 1) AS avg_click_rate
            FROM campaigns
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$weeks} WEEK)
            GROUP BY YEARWEEK(created_at, 1)
            ORDER BY week_key ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Campaigns with largest open→click drop-off (high gap = good subject, weak CTA).
    // Ordered by engagement_gap DESC so worst content/CTA problems surface first.
    public function engagementGap(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT id, campaign_name, campaign_type, sent_count,
                   open_rate, click_rate,
                   ROUND(open_rate - click_rate, 1) AS engagement_gap,
                   CASE WHEN open_rate > 0
                        THEN ROUND((click_rate / open_rate) * 100, 1)
                        ELSE 0 END AS ctr_ratio
            FROM campaigns
            WHERE status IN ('Sent', 'Completed')
              AND open_rate IS NOT NULL
              AND click_rate IS NOT NULL
            ORDER BY engagement_gap DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Summary stats for the dashboard stat cards.
    public function dashboardStats(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(status = 'Scheduled') AS scheduled,
                SUM(status IN ('Sent','Completed')) AS sent_completed,
                ROUND(AVG(CASE WHEN status IN ('Sent','Completed') AND open_rate  IS NOT NULL THEN open_rate  END), 1) AS avg_open_rate,
                ROUND(AVG(CASE WHEN status IN ('Sent','Completed') AND click_rate IS NOT NULL THEN click_rate END), 1) AS avg_click_rate
            FROM campaigns
        ");
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }

    // ── Private Helpers ────────────────────────────────────────────────────────

    private function buildWhere(string $q, array $statuses): array
    {
        $clauses = [];
        $params  = [];

        if ($q !== '') {
            $clauses[] = 'c.campaign_name LIKE ?';
            $params[]  = "%{$q}%";
        }

        $validStatuses = array_values(array_intersect($statuses, self::$statuses));
        if (!empty($validStatuses)) {
            $ph        = implode(',', array_fill(0, count($validStatuses), '?'));
            $clauses[] = "c.status IN ({$ph})";
            array_push($params, ...$validStatuses);
        }

        $sql = $clauses ? ' WHERE ' . implode(' AND ', $clauses) : '';
        return [$sql, $params];
    }
}
