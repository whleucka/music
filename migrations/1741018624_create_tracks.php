<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "tracks";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->char("hash", 32);
            $table->text("filename");
            $table->text("pathname");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->unique("hash");
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
         return Schema::drop($this->table);
    }
};
