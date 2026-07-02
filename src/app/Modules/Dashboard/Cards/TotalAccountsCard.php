<?php
namespace App\Modules\Dashboard\Cards;

use App\Modules\Dashboard\DashboardCard;

/**
 * OWNER: Max (Customer) — DROP-IN SLOT
 *
 * Stat card — total number of accounts.
 *
 * TODO (Max): replace the stub with a real query. Add to DashboardRepository:
 *
 *   public function accountCount(): int {
 *       // SELECT COUNT(*) FROM accounts
 *   }
 *
 * Consider a sub-line with contact count too (SELECT COUNT(*) FROM contacts).
 */
class TotalAccountsCard extends DashboardCard
{
    public function title(): string { return 'Total Accounts'; }

    public function permission(): ?string { return 'customers.view'; }

    public function body(): string
    {
        // STUB
        $accounts = 0;

        return $this->stat(
            $accounts,
            '0 contacts',
            '/modules/customer/accounts.php',
            'View accounts'
        );
    }
}
