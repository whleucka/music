<?php

use Echo\Interface\Database\Migration;

return new class implements Migration
{
    public function up(): string
    {
        return "INSERT INTO modules (link, title, icon, item_order) VALUES 
            ('dashboard', 'Dashboard', 'rocket', 0),
            ('modules', 'Modules', 'box', 10),
            ('users', 'Users', 'people', 20),
            ('user-permissions', 'User Permissions', 'shield-check', 30),
            ('sessions', 'Sessions', 'person-bounding-box', 40)";
    }

    public function down(): string
    {
        return "DELETE FROM modules WHERE link IN (
            'dashboard', 
            'modules', 
            'users', 
            'user-permissions', 
            'sessions'
        )";
    }
};
