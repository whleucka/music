<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/users", name_prefix: "users")]
class UsersController extends AdminController
{
    public function __construct()
    {
        $this->module_icon = "people";
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
            "Password" => "'' as password",
            "Password (again)" => "'' as password_match",
        ];

        $this->form_validation_rules = [
            "first_name" => ["required"],
            "surname" => ["required"],
            "email" => ["required", "email", "unique:users"],
            "password" => ["required", "min_length:10", "regex:^(?=.*[A-Z])(?=.*\W)(?=.*\d).+$"],
            "password_match" => ["required", "match:password"],
        ];
    }

    protected function handleStore(array $request)
    {
        unset($request["password_match"]);
        $request["password"] = password_hash($request['password'], PASSWORD_ARGON2I);
        parent::handleStore($request);
    }

    protected function handleUpdate(int $id, array $request)
    {
        unset($request["password_match"]);
        $request["password"] = password_hash($request['password'], PASSWORD_ARGON2I);
        parent::handleUpdate($id, $request);
    }
}
