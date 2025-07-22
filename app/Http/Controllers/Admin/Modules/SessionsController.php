<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group("/admin/sessions")]
class SessionsController extends AdminController
{
    public function __construct()
    {
        $this->module_icon = '<i class="bi bi-person-bounding-box pe-1"></i>';
        $this->module_title = "Sessions";
        $this->module_link = "sessions";

        $this->table_name = "sessions";
        $this->table_columns = [
            "URI" => "uri",
            "IP" => "INET_NTOA(ip)",
            "Created" => "created_at",
        ];
    }
}
