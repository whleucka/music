<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
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

    #[Get("/module/count", "module.count")]
    public function module_count(): int
    {
        return db()->execute("SELECT count(*) FROM modules WHERE parent_id IS NOT NULL")->fetchColumn();
    }

    #[Get("/requests/all-time", "requests.all-time")]
    public function requests_all_time(): int
    {
        return db()->execute("SELECT count(*) FROM sessions")->fetchColumn();
    }

    #[Get("/requests/today", "requests.today")]
    public function requests_today(): int
    {
        return db()->execute("SELECT count(*) FROM sessions WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    }

    #[Get("/sales", "sales")]
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
