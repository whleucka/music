<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group("/admin/sessions")]
class SessionsController extends AdminController
{
    protected string $module_icon = '<i class="bi bi-person-bounding-box pe-1"></i>';
    protected string $module_title = "Sessions";
    protected string $table_name = "sessions";
    protected array $table_columns = [
        "ID" => "id",
        "URI" => "uri",
        "IP" => "INET_NTOA(ip)",
        "Created" => "created_at",
    ];
}
