<?php

namespace App\Http\Controllers\Admin\Modules;

use Echo\Framework\Admin\Module;
use Echo\Framework\Admin\View\Table\Table;
use Echo\Framework\Admin\View\Table\Schema as TableSchema;

class Sessions extends Module
{
    protected string $module_icon = '<i class="bi bi-person-bounding-box pe-1"></i>';
    protected string $module_title = "Sessions";

    protected function indexContent(): string
    {
        return TableSchema::create("sessions", function(Table $table) {
            $table->columns = [
                "ID" => "id",
                "URI" => "uri",
                "IP" => "INET_NTOA(ip) as ip",
                "Created" => "created_at",
            ];
        });
    }
}
