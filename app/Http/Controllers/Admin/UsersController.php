<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/users", name_prefix: "users")]
class UsersController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "ID" => "id",
            "UUID" => "uuid",
            "Role" => "role",
            "Name" => "CONCAT(first_name, ' ', surname) as name",
            "Email" => "email",
            "Created" => "created_at",
        ];

        $this->search_columns = [
            "Email",
        ];

        $this->filter_dropdowns = [
            "role" => [
                [
                    "value" => "standard",
                    "label" => "Standard",
                ],
                [
                    "value" => "admin",
                    "label" => "Admin",
                ],
            ]
        ];

        $this->form_columns = [
            "Avatar" => "avatar",
            "Role" => "role",
            "First Name" => "first_name",
            "Surname" => "surname",
            "Email" => "email",
            "Password" => "'' as password",
            "Password (again)" => "'' as password_match",
        ];

        $this->form_controls = [
            "avatar" => "image",
            "role" => "dropdown",
            "first_name" => "input",
            "surname" => "input",
            "email" => "email",
            "password" => "password",
            "password_match" => "password",
        ];

        $this->form_dropdowns = [
            "role" => [
                [
                    "value" => "standard",
                    "label" => "Standard",
                ],
                [
                    "value" => "admin",
                    "label" => "Admin",
                ],
            ]
        ];

        $this->validation_rules = [
            "avatar" => [],
            "role" => ["required"],
            "first_name" => ["required"],
            "surname" => [],
            "email" => ["required", "email", "unique:users"],
            "password" => ["required", "min_length:10", "regex:^(?=.*[A-Z])(?=.*\W)(?=.*\d).+$"],
            "password_match" => ["required", "match:password"],
        ];

        parent::__construct("users");
    }

    public function validate(array $ruleset = [], mixed $id = null): mixed
    {
        if ($id) {
            $ruleset = $this->removeValidationRule($ruleset, "email", "unique:users");
        }
        return parent::validate($ruleset);
    }

    protected function hasDelete(int $id): bool
    {
        if ($id === $this->user->id) return false;
        return parent::hasDelete($id);
    }

    protected function handleStore(array $request): mixed
    {
        unset($request["password_match"]);
        $request["password"] = password_hash($request['password'], PASSWORD_ARGON2I);
        return parent::handleStore($request);
    }

    protected function handleUpdate(int $id, array $request): bool
    {
        unset($request["password_match"]);
        $request["password"] = password_hash($request['password'], PASSWORD_ARGON2I);
        return parent::handleUpdate($id, $request);
    }
}
