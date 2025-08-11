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
            "Order" => "item_order",
        ];

        $this->form_controls = [
            "link" => "input",
            "title" => "input",
            "icon" => "input",
            "item_order" => "number",
        ];

        $this->query_order_by = [
            "item_order ASC",
        ];

        $this->search_columns = [
            "Title",
        ];

        $this->filter_links = [
            "Root Nodes" => "parent_id IS NULL",
            "Leaf Nodes" => "parent_id IS NOT NULL",
        ];

        $this->validation_rules = [
            "link" => [],
            "title" => ["required"],
            "icon" => [],
        ];

        parent::__construct("modules");
    }
}
