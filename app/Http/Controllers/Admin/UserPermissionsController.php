<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserPermission;
use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/user-permissions", name_prefix: "user-permissions")]
class UserPermissionsController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "Module" => "(SELECT title FROM modules WHERE modules.id = module_id) as module_id",
            "User" => "(SELECT CONCAT(first_name, ' ', surname) FROM users WHERE users.id = user_id) as user_id",
            "Create" => "has_create",
            "Edit" => "has_edit",
            "Delete" => "has_delete",
            "Export CSV" => "has_export",
        ];

        $this->table_format = [
            "has_create" => "check",
            "has_edit" => "check",
            "has_delete" => "check",
            "has_export" => "check",
        ];

        $this->filter_dropdowns = [
            "module_id" => "SELECT id as value, title as label FROM modules WHERE parent_id IS NOT NULL AND enabled = 1 ORDER BY label",
            "user_id" => "SELECT id as value, CONCAT(first_name, ' ', surname) as label FROM users WHERE role != 'admin' ORDER BY label",
        ];

        $this->form_columns = [
            "Module" => "module_id",
            "User" => "user_id",
            "Create" => "has_create",
            "Edit" => "has_edit",
            "Delete" => "has_delete",
            "Export CSV" => "has_export",
        ];

        $this->form_controls = [
            "module_id" => "dropdown",
            "user_id" => "dropdown",
            "has_create" => "checkbox",
            "has_edit" => "checkbox",
            "has_delete" => "checkbox",
            "has_export" => "checkbox",
        ];

        $this->form_dropdowns = [
            "module_id" => "SELECT id as value, title as label FROM modules WHERE parent_id IS NOT NULL AND enabled = 1 ORDER BY title",
            "user_id" => "SELECT id as value, CONCAT(first_name, ' ', surname) as label FROM users WHERE role != 'admin' ORDER BY label",
        ];

        $this->validation_rules = [
            "module_id" => ["required"],
            "user_id" => ["required"],
            "has_create" => [],
            "has_edit" => [],
            "has_delete" => [],
            "has_export" => [],
        ];

        parent::__construct("user_permissions");
    }

    public function validate(array $ruleset = [], mixed $id = null): mixed
    {
        $request = parent::validate($ruleset, $id);
        // Parent validation succeeds
        if ($request) {
            $module_id = $request->module_id;
            $user_id = $request->user_id;
            $user = User::find($user_id);
            if ($user) {
                $exists = $user->hasPermission($module_id);
                if ($id) {
                    // Update validation
                    $user_permission = UserPermission::find($id);
                    if ($exists && $user_permission && $user_permission->module_id != $request->module_id) {
                        $this->addValidationError("module_id", "This user already has permission to this module");
                        return null;
                    }
                } else if ($exists) {
                    // Create validation
                    $this->addValidationError("module_id", "This user already has permission to this module");
                    return null;
                }
            }
        }
        return $request;
    }
}
