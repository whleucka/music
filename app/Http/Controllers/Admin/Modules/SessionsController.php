<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/sessions", name_prefix: "sessions")]
class SessionsController extends AdminController
{
    public function __construct()
    {
        $this->has_edit = $this->has_delete = false;

        $this->table_columns = [
            "User" => "(SELECT email FROM users WHERE users.id = user_id) as user",
            "IP" => "INET_NTOA(ip)",
            "URI" => "uri",
            "Created" => "created_at",
        ];

        $this->filter_links = [
            "All" => "1=1",
            "Me" => "user_id = " . user()->id,
            "Others" => "user_id != " . user()->id,
        ];

        $this->search_columns = [
            "URI",
            "User",
        ];

        parent::__construct("sessions");
    }
}
