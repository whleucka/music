<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\Get;

#[Group(path_prefix: "/dashboard", name_prefix: "dashboard")]
class DashboardController extends AdminController
{
    #[Get("/users/count", "users.count")]
    public function users_count(): int
    {
        return db()->execute("SELECT count(*) 
            FROM users")->fetchColumn();
    }

    #[Get("/users/active", "users.active")]
    public function users_active(): int
    {
        return db()->execute("SELECT COUNT(DISTINCT user_id) AS active_users
            FROM sessions
            WHERE created_at >= NOW() - INTERVAL 30 MINUTE AND 
            user_id IS NOT NULL;")->fetchColumn();
    }
    

    #[Get("/customers/count", "customers.count")]
    public function customers_count(): int
    {
        return 0;
    }

    #[Get("/customers/new", "customers.new")]
    public function customers_new(): int
    {
        return 0;
    }

    #[Get("/modules/count", "modules.count")]
    public function modules_count(): int
    {
        return db()->execute("SELECT count(*) 
            FROM modules 
            WHERE parent_id IS NOT NULL")->fetchColumn();
    }

    #[Get("/requests/count/total", "requests.total")]
    public function requests_total(): int
    {
        return db()->execute("SELECT count(*) 
            FROM sessions
            WHERE user_id IS NULL")->fetchColumn();
    }

    #[Get("/requests/count/today", "requests.today")]
    public function requests_today(): int
    {
        return db()->execute("SELECT count(*) 
            FROM sessions 
            WHERE DATE(created_at) = CURDATE() AND
            user_id IS NULL")->fetchColumn();
    }

    #[Get("/requests/chart/today", "requests.today.chart", ["max_requests" => 0])]
    public function requests_today_chart()
    {
        $data = db()->fetchAll("SELECT 
            HOUR(created_at) AS hour,
            COUNT(*) AS total
            FROM sessions
            WHERE DATE(created_at) = CURDATE() AND
            user_id IS NULL
            GROUP BY HOUR(created_at)
            ORDER BY hour");
        $hours = range(0, 23);
        $payload = array_fill(0, 24, 0);
        foreach ($data as $row) {
            $payload[(int)$row['hour']] = (int)$row['total'];
        }
        $labels = array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ":00", $hours);
        return $this->render('admin/dashboard-chart.html.twig', [
            'id' => 'requests-chart-today',
            'options' => json_encode([
                'type' => 'line',
                'data' => (object)[
                    'labels' => $labels,
                    'datasets' => [
                        (object)[
                            'label' => 'Today',
                            'data' => $payload,
                            'fill' => false,
                            'backgroundColor' => 'rgba(0, 94, 255, 0.5)',
                            'borderColor' => 'rgb(0, 94, 255)',
                            'borderWidth' => 2,
                            'tension' => 0.1,
                        ]
                    ]
                ],
                'options' => (object)[
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => (object)[
                        'y' => (object)[
                            'beginAtZero' => true,
                        ]
                    ],
                ],
            ]),
        ]);
    }

    #[Get("/requests/chart/week", "requests.week.chart", ["max_requests" => 0])]
    public function requests_week_chart()
    {
        $data = db()->fetchAll("SELECT 
                DAYNAME(created_at) AS day_name,
                DATE(created_at) AS day_date,
                COUNT(*) AS total
            FROM sessions
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) AND
            user_id IS NULL
            GROUP BY day_date
            ORDER BY day_date");
        $labels = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $payload = array_fill(0, 7, 0);
        foreach ($data as $row) {
            $index = array_search($row['day_name'], $labels);
            if ($index !== false) {
                $payload[$index] = (int)$row['total'];
            }
        }
        return $this->render('admin/dashboard-chart.html.twig', [
            'id' => 'requests-chart-week',
            'options' => json_encode([
                'type' => 'bar',
                'data' => (object)[
                    'labels' => $labels,
                    'datasets' => [
                        (object)[
                            'label' => 'Current Week',
                            'data' => $payload,
                            'fill' => false,
                            'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                            'borderColor' => 'rgb(255, 159, 64)',
                            'borderWidth' => 2,
                        ]
                    ]
                ],
                'options' => (object)[
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => (object)[
                        'y' => (object)[
                            'beginAtZero' => true,
                        ]
                    ],
                ],
            ]),
        ]);
    }

    #[Get("/requests/chart/month", "requests.month.chart", ["max_requests" => 0])]
    public function requests_month_chart()
    {
        $data = db()->fetchAll("SELECT 
            DAY(created_at) AS day_number,
            COUNT(*) AS total
            FROM sessions
            WHERE YEAR(created_at) = YEAR(CURDATE()) AND 
            MONTH(created_at) = MONTH(CURDATE()) AND
            user_id IS NULL
            GROUP BY day_number
            ORDER BY day_number");
        $daysInMonth = date('t');
        $labels = range(1, $daysInMonth);
        $payload = array_fill(0, $daysInMonth, 0);
        foreach ($data as $row) {
            $payload[$row['day_number'] - 1] = (int)$row['total'];
        }
        return $this->render('admin/dashboard-chart.html.twig', [
            'id' => 'requests-chart-month',
            'options' => json_encode([
                'type' => 'bar',
                'data' => (object)[
                    'labels' => $labels,
                    'datasets' => [
                        (object)[
                            'label' => 'Current Month',
                            'data' => $payload,
                            'fill' => false,
                            'backgroundColor' => 'rgba(153, 102, 255, 0.5)',
                            'borderColor' => 'rgb(153, 102, 255)',
                            'borderWidth' => 2,
                        ]
                    ]
                ],
                'options' => (object)[
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => (object)[
                        'y' => (object)[
                            'beginAtZero' => true,
                        ]
                    ],
                ],
            ]),
        ]);
    }

    #[Get("/requests/chart/ytd", "requests.ytd.chart", ["max_requests" => 0])]
    public function requests_ytd_chart()
    {
        $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
            FROM sessions
            WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND
            user_id IS NULL
            GROUP BY month
            ORDER BY month");
        $labels = [];
        $payload = [];
        foreach ($data as $row) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $payload[] = (int)$row['total'];
        }
        return $this->render('admin/dashboard-chart.html.twig', [
            'id' => 'requests-chart-ytd',
            'options' => json_encode([
                'type' => 'bar',
                'data' => (object)[
                    'labels' => $labels,
                    'datasets' => [
                        (object)[
                            'label' => 'Year to Date',
                            'data' => $payload,
                            'fill' => false,
                            'backgroundColor' => 'rgba(91, 235, 52, 0.5)',
                            'borderColor' => 'rgb(91, 235, 52)',
                            'borderWidth' => 2,
                        ]
                    ]
                ],
                'options' => (object)[
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => (object)[
                        'y' => (object)[
                            'beginAtZero' => true,
                        ]
                    ],
                ],
            ]),
        ]);
    }

    #[Get("/sales/total", "sales.total")]
    public function sales(): string
    {
        return '$' . number_format(0,2);
    }

    #[Get("/sales/today", "sales.today")]
    public function sales_today(): string
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
