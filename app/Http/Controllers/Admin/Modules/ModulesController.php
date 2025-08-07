<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Routing\Group;

#[Group(path_prefix: "/modules", name_prefix: "modules")]
class ModulesController extends AdminController
{
    public function __construct()
    {
        $this->table_columns = [
            "ID" => "id",
            "Link" => "link",
            "Title" => "title",
            "Icon" => "icon",
            "Roles" => "roles",
            "Updated" => "updated_at",
            "Created" => "created_at",
        ];

        $this->form_columns = [
            "Link" => "link",
            "Title" => "title",
            "Icon" => "icon",
            "Roles" => "roles",
        ];

        $this->form_controls = [
            "link" => "input",
            "title" => "input",
            "icon" => "input",
            "roles" => "input",
        ];

        $this->search_columns = [
            "Roles",
            "Title",
        ];

        $this->validation_rules = [
            "link" => ["required"],
            "title" => ["required"],
            "icon" => ["required"],
            "roles" => ["required"],
        ];

        parent::__construct("modules");
    }
}
