<?php
namespace App\Modules\Dashboard;

class DashboardService
{
    public function metrics(): array
    {
        return [
            'customers' => 0,
            'active_rfqs' => 0,
            'campaigns' => 0,
            'low_stock' => 0,
        ];
    }
}
