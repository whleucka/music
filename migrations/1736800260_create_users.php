<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "users";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->default("(UUID())");
            $table->varchar("first_name");
            $table->varchar("surname");
            $table->varchar("email");
            $table->varchar("role")->default("standard");
            $table->binary("password", 96);
            $table->timestamps();
            $table->unique("email");
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
         return Schema::drop($this->table);
    }
};
