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

        $this->table_pk = "sessions.id";

        $this->table_columns = [
            "ID" => "sessions.id",
            "User" => "users.email",
            "IP" => "INET_NTOA(sessions.ip) as ip",
            "URI" => "sessions.uri",
            "Created" => "sessions.created_at",
        ];

        $this->table_joins = [
            "LEFT JOIN users ON users.id = sessions.user_id"
        ];

        $this->filter_dropdowns = [
            "user_id" => "SELECT id as value, CONCAT(first_name, ' ', surname) as label FROM users ORDER BY label",
        ];

        $this->filter_links = [
            "Frontend" => "LEFT(sessions.uri, 6) != '/admin'",
            "Backend" => "LEFT(sessions.uri, 6) = '/admin'",
            "Me" => "user_id = " . user()->id,
        ];

        $this->search_columns = [
            "URI",
            "User",
        ];

        parent::__construct("sessions");
    }
}
