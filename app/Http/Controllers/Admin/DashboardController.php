<?php

namespace App\Http\Controllers\Admin;

use App\Providers\Auth\DashboardService;
use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;
use Echo\Framework\Routing\Route\Get;

#[Group(path_prefix: "/dashboard", name_prefix: "dashboard")]
class DashboardController extends AdminController
{
    public function __construct(private DashboardService $provider) 
    {
        parent::__construct();
    }

    #[Get("/sales/total", "sales.total")]
    public function sales(): string
    {
        return $this->provider->getTotalSales();
    }

    #[Get("/sales/today", "sales.today")]
    public function sales_today(): string
    {
        return $this->provider->getTodaySales();
    }

    #[Get("/users/count", "users.count")]
    public function users_count(): int
    {
        return $this->provider->getUsersCount();
    }

    #[Get("/users/active", "users.active")]
    public function users_active(): int
    {
        return $this->provider->getActiveUsersCount();
    }


    #[Get("/customers/count", "customers.count")]
    public function customers_count(): int
    {
        return $this->provider->getCustomersCount();
    }

    #[Get("/customers/new", "customers.new")]
    public function customers_new(): int
    {
        return $this->provider->getNewCustomersCount();
    }

    #[Get("/modules/count", "modules.count")]
    public function modules_count(): int
    {
        return $this->provider->getModulesCount();
    }

    #[Get("/requests/count/total", "requests.total")]
    public function requests_total(): int
    {
        return $this->provider->getTotalRequests();
    }

    #[Get("/requests/count/today", "requests.today")]
    public function requests_today(): int
    {
        return $this->provider->getTodayRequests();
    }

    #[Get("/requests/chart/today", "requests.today.chart", ["max_requests" => 0])]
    public function requests_today_chart()
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getTodayRequestsChart());
    }

    #[Get("/requests/chart/week", "requests.week.chart", ["max_requests" => 0])]
    public function requests_week_chart()
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getWeekRequestsChart());
    }

    #[Get("/requests/chart/month", "requests.month.chart", ["max_requests" => 0])]
    public function requests_month_chart()
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getMonthRequestsChart());
    }

    #[Get("/requests/chart/ytd", "requests.ytd.chart", ["max_requests" => 0])]
    public function requests_ytd_chart()
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getYTDRequestsChart());
    }

    protected function renderTable(): string
    {
        return $this->render("admin/dashboard.html.twig", [
            ...$this->getCommonData(),
        ]);
    }
}
