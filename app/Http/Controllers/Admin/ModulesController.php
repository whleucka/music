<?php

namespace App\Http\Controllers\Admin;

use Echo\Framework\Http\AdminController;
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

        $this->form_columns = [
            "Link" => "link",
            "Title" => "title",
            "Icon" => "icon",
            "Order" => "item_order",
        ];

        $url = config("paths.js") . '/bootstrap-icons.json';
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        $this->form_datalist = [
            "icon" => array_keys($data),
        ];

        $this->form_controls = [
            "link" => "input",
            "title" => "input",
            "icon" => "input",
            "item_order" => "number",
        ];

        $this->validation_rules = [
            "link" => [],
            "title" => ["required"],
            "icon" => [],
        ];

        parent::__construct("modules");
    }
}
