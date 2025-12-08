<?php

use Echo\Interface\Database\Migration;
use Echo\Framework\Database\{Schema, Blueprint};

return new class implements Migration
{
    private string $table = "radio_stations";

    public function up(): string
    {
         return Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->varchar("name");
            $table->varchar("country")->nullable();
            $table->varchar("province")->nullable();
            $table->varchar("city")->nullable();
            $table->varchar("cover");
            $table->varchar("src");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
         return Schema::drop($this->table);
    }
};
