<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/dashboard", name_prefix: "dashboard")]
class DashboardController extends AdminController
{
    protected function renderTable(): string
    {
        return $this->render("admin/dashboard.html.twig", [
            ...$this->getCommonData(),
            "user_count" => db()->execute("SELECT count(*) FROM users")->fetchColumn(),
            "session_count" => db()->execute("SELECT count(*) FROM sessions")->fetchColumn(),
            "module_count" => db()->execute("SELECT count(*) FROM modules")->fetchColumn(),
            "sales" => number_format(0, 2),
        ]);
    }
}
