<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/dashboard", name_prefix: "dashboard")]
class DashboardController extends AdminController
{
    public function __construct()
    {
        $this->module_icon = "rocket";
        $this->module_title = "Dashboard";
        $this->module_link = "dashboard";
    }
}
