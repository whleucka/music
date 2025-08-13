<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/activity", name_prefix: "activity")]
class ActivityController extends AdminController
{
    public function __construct()
    {
        $this->has_create = $this->has_edit = $this->has_delete = false;

        $this->table_columns = [
            "User" => "(SELECT email FROM users WHERE users.id = user_id) as user",
            "IP" => "INET_NTOA(ip)",
            "URI" => "uri",
            "Created" => "created_at",
        ];

        $this->filter_links = [
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
