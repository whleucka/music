<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/admin/dashboard", name_prefix: "dashboard", middleware: ["auth"])]
class DashboardController extends AdminController
{
    public function __construct()
    {
        $this->module_icon = '<i class="bi bi-rocket pe-1"></i>';
        $this->module_title = "Dashboard";
        $this->module_link = "dashboard";
    }
}
