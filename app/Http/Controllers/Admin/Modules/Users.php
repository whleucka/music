<?php

namespace App\Http\Controllers\Admin\Modules;

use Echo\Framework\Admin\Module;
use Echo\Framework\Admin\View\Table\Table;
use Echo\Framework\Admin\View\Table\Schema as TableSchema;

class Users extends Module
{
    protected string $module_icon = '<i class="bi bi-people pe-1"></i>';
    protected string $module_title = "Users";

    protected function indexContent(): string
    {
        return TableSchema::create("users", function(Table $table) {
            $table->columns = [
                "ID" => "id",
                "UUID" => "uuid",
                "Name" => "CONCAT(first_name, ' ', surname)",
                "Email" => "email",
                "Created" => "created_at",
                "Updated" => "updated_at",
            ];
        });
    }
}
