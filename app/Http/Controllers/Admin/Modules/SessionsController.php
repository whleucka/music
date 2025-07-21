<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Admin\AdminController;
use Echo\Framework\Admin\View\Table\Table;
use Echo\Framework\Admin\View\Table\Schema as TableSchema;

class SessionsController extends AdminController
{
    protected string $module_icon = '<i class="bi bi-person-bounding-box pe-1"></i>';
    protected string $module_title = "Sessions";

    protected function indexContent(string $module): string
    {
        return TableSchema::create("sessions", function(Table $table) use ($module) {
            $table->module = $module;
            $table->columns = [
                "ID" => "id",
                "URI" => "uri",
                "IP" => "INET_NTOA(ip) as ip",
                "Created" => "created_at",
            ];
        });
    }
}
