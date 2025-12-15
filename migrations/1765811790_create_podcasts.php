<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "podcasts";

    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->char("hash", 32);
            $table->unsignedBigInteger("user_id");
            $table->varchar("thumbnail");
            $table->varchar("title");
            $table->text("description");
            $table->varchar("publisher");
            $table->varchar("website");
            $table->timestamps();
            $table->unique("hash");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};

