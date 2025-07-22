<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/admin/users", name_prefix: "users", middleware: ["auth"])]
class UsersController extends AdminController
{
    public function __construct()
    {
        $this->module_icon =  '<i class="bi bi-people pe-1"></i>';
        $this->module_title = "Users";
        $this->module_link = "users";

        $this->table_name = "users";
        $this->table_columns = [
            "ID" => "id",
            "UUID" => "uuid",
            "Name" => "CONCAT(first_name, ' ', surname) as name",
            "Email" => "email",
            "Created" => "created_at",
        ];

        $this->form_columns = [
            "First Name" => "first_name",
            "Surname" => "surname",
            "Email" => "email",
        ];
    }
}
