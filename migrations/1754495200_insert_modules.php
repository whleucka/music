<?php

use Echo\Interface\Database\Migration;

return new class implements Migration
{
    public function up(): string
    {
        return "INSERT INTO modules (link, title, icon, roles, item_order) VALUES 
            ('dashboard', 'Dashboard', 'rocket', 'admin,standard', 0),
            ('modules', 'Modules', 'box', 'admin', 10),
            ('users', 'Users', 'people', 'admin', 20),
            ('sessions', 'Sessions', 'person-bounding-box', 'admin', 30)";
    }

    public function down(): string
    {
        return "DELETE FROM modules WHERE link IN ('dashboard', 'users', 'sessions')";
    }
};
