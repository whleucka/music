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
            "User" => "(SELECT email FROM users WHERE users.id = user_id) as user_id",
            "IP" => "INET_NTOA(ip) as ip",
            "URI" => "uri",
            "Created" => "created_at",
        ];

        $this->filter_dropdowns = [
            "user_id" => "SELECT id as value, CONCAT(first_name, ' ', surname) as label FROM users ORDER BY label",
        ];

        $this->filter_links = [
            "Frontend" => "user_id IS NULL",
            "Backend" => "user_id IS NOT NULL",
            "Me" => "user_id = " . user()->id,
        ];

        $this->search_columns = [
            "URI",
            "User",
        ];

        parent::__construct("sessions");
    }
}
