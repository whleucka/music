<?php

namespace Echo\Framework\Admin\View\Table;

use Closure;

class Schema
{
    public static function create(string $table_name, Closure $callback): string
    {
        $table = new Table($table_name);
        $callback($table);
        $data = $table->build();
        return $table->render($data);
    }
}
