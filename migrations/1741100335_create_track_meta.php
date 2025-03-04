<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "track_meta";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("track_id");
            $table->varchar("cover");
            $table->varchar("artist");
            $table->varchar("album");
            $table->varchar("title");
            $table->varchar("genre");
            $table->varchar("year");
            $table->varchar("track_number");
            $table->varchar("playtime_string");
            $table->varchar("bitrate");
            $table->varchar("mime_type");
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("track_id")
                  ->references("tracks", "id")
                  ->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
         return Schema::drop($this->table);
    }
};
