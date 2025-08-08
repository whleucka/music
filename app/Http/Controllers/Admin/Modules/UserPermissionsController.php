<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/user-permissions", name_prefix: "user-permissions")]
class UserPermissionsController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "Module" => "(SELECT title FROM modules WHERE modules.id = module_id) as module",
            "User" => "(SELECT CONCAT(first_name, ' ', surname) FROM users WHERE users.id = user_id) as user",
            "Create" => "has_create",
            "Edit" => "has_edit",
            "Delete" => "has_delete",
        ];

        $this->table_format = [
            "has_create" => "check",
            "has_edit" => "check",
            "has_delete" => "check",
        ];

        $this->form_columns = [
            "Module" => "module_id",
            "User" => "user_id",
            "Create" => "has_create",
            "Edit" => "has_edit",
            "Delete" => "has_delete",
        ];

        $this->form_controls = [
            "module_id" => "dropdown",
            "user_id" => "dropdown",
            "has_create" => "checkbox",
            "has_edit" => "checkbox",
            "has_delete" => "checkbox",
        ];

        $this->validation_rules = [
            "module_id" => ["required"],
            "user_id" => ["required"],
            "has_create" => [],
            "has_edit" => [],
            "has_delete" => [],
        ];

        $this->dropdowns = [
            "module_id" => "SELECT id as value, title as label FROM modules ORDER BY title",
            "user_id" => "SELECT id as value, CONCAT(first_name, ' ', surname) as label FROM users ORDER BY label",
        ];

        parent::__construct("user_permissions");
    }
}
