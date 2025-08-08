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
            "Updated" => "updated_at",
            "Created" => "created_at",
        ];

        $this->table_format = [
            "icon" => fn($column, $value) => "<i class='bi bi-$value' />",
        ];

        $this->form_columns = [
            "Link" => "link",
            "Title" => "title",
            "Icon" => "icon",
        ];

        $this->form_controls = [
            "link" => "input",
            "title" => "input",
            "icon" => "input",
        ];

        $this->search_columns = [
            "Title",
        ];

        $this->validation_rules = [
            "link" => ["required"],
            "title" => ["required"],
            "icon" => ["required"],
        ];

        parent::__construct("modules");
    }
}
