<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "playlist_tracks";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("playlist_id");
            $table->unsignedBigInteger("track_id");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
            $table->unique("playlist_id,track_id");
            $table->foreignKey("playlist_id")
                  ->references("playlists", "id")
                  ->onDelete("CASCADE");
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
