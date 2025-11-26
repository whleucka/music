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

    #[Get("/tracks/count", "tracks.count")]
    public function tracks_count(): string
    {
        return number_format($this->provider->getTracksCount());
    }

    #[Get("/albums/count", "albums.count")]
    public function albums_count(): string
    {
        return number_format($this->provider->getAlbumsCount());
    }

    #[Get("/artists/count", "artists.count")]
    public function artists_count(): string
    {
        return number_format($this->provider->getArtistsCount());
    }

    #[Get("/users/count", "users.count")]
    public function users_count(): string
    {
        return number_format($this->provider->getUsersCount());
    }

    #[Get("/users/active", "users.active")]
    public function users_active(): string
    {
        return number_format($this->provider->getActiveUsersCount());
    }


    #[Get("/customers/count", "customers.count")]
    public function customers_count(): string
    {
        return number_format($this->provider->getCustomersCount());
    }

    #[Get("/customers/new", "customers.new")]
    public function customers_new(): string
    {
        return number_format($this->provider->getNewCustomersCount());
    }

    #[Get("/modules/count", "modules.count")]
    public function modules_count(): string
    {
        return number_format($this->provider->getModulesCount());
    }

    #[Get("/requests/count/total", "requests.total")]
    public function requests_total(): string
    {
        return number_format($this->provider->getTotalRequests());
    }

    #[Get("/requests/count/today", "requests.today")]
    public function requests_today(): string
    {
        return number_format($this->provider->getTodayRequests());
    }

    #[Get("/requests/chart/today", "requests.today.chart", ["max_requests" => 0])]
    public function requests_today_chart(): string
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getTodayRequestsChart());
    }

    #[Get("/requests/chart/week", "requests.week.chart", ["max_requests" => 0])]
    public function requests_week_chart(): string
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getWeekRequestsChart());
    }

    #[Get("/requests/chart/month", "requests.month.chart", ["max_requests" => 0])]
    public function requests_month_chart(): string
    {
        return $this->render('admin/dashboard-chart.html.twig', $this->provider->getMonthRequestsChart());
    }

    #[Get("/requests/chart/ytd", "requests.ytd.chart", ["max_requests" => 0])]
    public function requests_ytd_chart(): string
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
