<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/admin/sessions", name_prefix: "sessions", middleware: ["auth"])]
class SessionsController extends AdminController
{
    public function __construct()
    {
        $this->has_delete = false;

        $this->module_icon = '<i class="bi bi-person-bounding-box pe-1"></i>';
        $this->module_title = "Sessions";
        $this->module_link = "sessions";

        $this->table_name = "sessions";
        $this->table_columns = [
            "User" => "(SELECT email FROM users WHERE users.id = user_id) as user",
            "IP" => "INET_NTOA(ip)",
            "URI" => "uri",
            "Created" => "created_at",
        ];
    }
}
