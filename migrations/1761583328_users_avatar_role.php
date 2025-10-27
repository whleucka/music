<?php

use Echo\Interface\Database\Migration;

return new class implements Migration
{
    public function up(): string
    {
        return "ALTER TABLE users ADD avatar bigint unsigned, ADD role varchar(255) DEFAULT 'standard'";
    }

    public function down(): string
    {
         return "ALTER TABLE users DROP COLUMN avatar, DROP COLUMN role";
    }
};
