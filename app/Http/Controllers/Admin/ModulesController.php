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
            "Enabled" => "enabled",
            "Link" => "link",
            "Title" => "title",
            "Icon" => "icon",
            "Created" => "created_at",
        ];

        $this->table_format = [
            "icon" => fn($column, $value) => "<i class='bi bi-$value' />",
            "enabled" => "check",
        ];

        $this->query_order_by = "item_order";
        $this->query_sort = "ASC";

        $this->search_columns = [
            "Title",
        ];

        $this->filter_links = [
            "Parents" => "parent_id IS NULL",
            "Children" => "parent_id IS NOT NULL",
        ];

        $this->form_columns = [
            "Enabled" => "enabled",
            "Parent" => "parent_id",
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
            "enabled" => "checkbox",
            "parent_id" => "dropdown",
            "link" => "input",
            "title" => "input",
            "icon" => "input",
            "item_order" => "number",
        ];

        $this->form_dropdowns = [
            "parent_id" => "SELECT id as value, if(parent_id IS NULL, concat(title, ' (root)'), title) as label 
                FROM modules 
                ORDER BY parent_id IS NULL DESC, title",
        ];

        $this->validation_rules = [
            "enabled" => [],
            "parent_id" => [],
            "link" => [],
            "title" => ["required"],
            "icon" => [],
        ];

        parent::__construct("modules");
    }

    protected function handleUpdate(int $id, array $request): bool
    {
        $result = parent::handleUpdate($id, $request);
        if ($result) $this->hxTrigger("loadSidebar");
        return $result;
    }

    protected function handleStore(array $request): mixed
    {
        $result = parent::handleStore($request);
        if ($result) $this->hxTrigger("loadSidebar");
        return $result;
    }

    protected function handleDestroy(int $id): bool
    {
        $result = parent::handleDestroy($id);
        if ($result) $this->hxTrigger("loadSidebar");
        return $result;
    }
}
