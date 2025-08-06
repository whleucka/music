<?php

use Echo\Interface\Database\Migration;

return new class implements Migration
{
    public function up(): string
    {
        return "INSERT INTO modules (link, title, icon) VALUES 
            ('dashboard', 'Dashboard', 'rocket'),
            ('users', 'Users', 'people'),
            ('sessions', 'Sessions', 'person-bounding-box')";
    }

    public function down(): string
    {
        return "DELETE FROM modules WHERE link IN ('dashboard', 'users', 'sessions')";
    }
};
