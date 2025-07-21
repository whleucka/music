<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group("/admin/dashboard")]
class DashboardController extends AdminController
{
    protected string $module_icon = '<i class="bi bi-rocket pe-1"></i>';
    protected string $module_title = "Dashboard";
}
