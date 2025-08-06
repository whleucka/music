<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "modules";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->varchar("link");
            $table->varchar("title");
            $table->varchar("icon");
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("parent_id")->references("modules", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
         return Schema::drop($this->table);
    }
};
