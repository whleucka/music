<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group("/admin/users")]
class UsersController extends AdminController
{
    protected string $module_icon = '<i class="bi bi-people pe-1"></i>';
    protected string $module_title = "Users";
    protected string $table_name = "users";
    protected array $table_columns = [
        "ID" => "id",
        "UUID" => "uuid",
        "Name" => "CONCAT(first_name, ' ', surname) as name",
        "Email" => "email",
        "Created" => "created_at",
    ];
}
