<?php

use Echo\Interface\Database\Migration;

return new class implements Migration
{
    public function up(): string
    {
        return "INSERT INTO modules (link, title, icon, item_order) VALUES 
            ('dashboard', 'Dashboard', 'rocket', 0),
            ('users', 'Users', 'people', 10),
            ('sessions', 'Sessions', 'person-bounding-box', 20)";
    }

    public function down(): string
    {
        return "DELETE FROM modules WHERE link IN ('dashboard', 'users', 'sessions')";
    }
};
