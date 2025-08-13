<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\Get;

#[Group(path_prefix: "/dashboard", name_prefix: "dashboard")]
class DashboardController extends AdminController
{
    #[Get("/user/count", "user.count")]
    public function user_count(): int
    {
        return db()->execute("SELECT count(*) FROM users")->fetchColumn();
    }

    #[Get("/customer/count", "customer.count")]
    public function customer_count(): int
    {
        return 0;
    }

    #[Get("/module/count", "module.count")]
    public function module_count(): int
    {
        return db()->execute("SELECT count(*) FROM modules WHERE parent_id IS NOT NULL")->fetchColumn();
    }

    #[Get("/requests/count/all-time", "requests.all-time")]
    public function requests_all_time(): int
    {
        return db()->execute("SELECT count(*) FROM sessions")->fetchColumn();
    }

    #[Get("/requests/count/today", "requests.today")]
    public function requests_today(): int
    {
        return db()->execute("SELECT count(*) FROM sessions WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    }

    #[Get("/requests/chart/today", "requests.today.chart", ["api"])]
    public function requests_today_chart()
    {
        $data = db()->fetchAll("SELECT 
            HOUR(created_at) AS hour,
            COUNT(*) AS total
            FROM sessions
            WHERE DATE(created_at) = CURDATE()
            GROUP BY HOUR(created_at)
            ORDER BY hour");
        $hours = range(0, 23);
        $chartData = array_fill(0, 24, 0);
        foreach ($data as $row) {
            $chartData[(int)$row['hour']] = (int)$row['total'];
        }
        return [
            'labels' => array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ":00", $hours),
            'payload' => $chartData
        ];
    }

    #[Get("/requests/chart/ytd", "requests.ytd.chart", ["api"])]
    public function requests_ytd_chart()
    {
        $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
            FROM sessions
            WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-01-01')
            GROUP BY month
            ORDER BY month");
        $labels = [];
        $counts = [];
        foreach ($data as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01')); // e.g. "Jan 2025"
            $counts[] = (int)$row['total'];
        }
        return [
            'labels' => $labels,
            'payload' => $counts
        ];
    }

    #[Get("/sales/total", "sales")]
    public function sales(): string
    {
        return '$' . number_format(0,2);
    }

    protected function renderTable(): string
    {
        return $this->render("admin/dashboard.html.twig", [
            ...$this->getCommonData(),
        ]);
    }
}
