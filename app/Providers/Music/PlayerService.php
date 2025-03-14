<?php

namespace App\Providers\Music;

class PlayerService
{
    public function getPlayer(): array
    {
        return session()->get("player") ?? [
            "id" => "",
            "source" => "",
            "cover" => "/images/no-album.png",
            "artist" => "",
            "album" => "",
            "title" => "",
        ];
    }
}
