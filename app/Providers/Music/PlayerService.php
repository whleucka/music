<?php

namespace App\Providers\Music;

use App\Models\Track;
use App\Models\TrackMeta;

class PlayerService
{
    public function setPlayer(string $source, string $cover, string $artist, string $album, string $title): void
    {
        $player = [
            "source" => $source,
            "cover" => $cover,
            "artist" => $artist,
            "album" => $album,
            "title" => $title,
        ];
        session()->set("player", $player);
    }

    public function getPlayer(): array
    {
        return session()->get("player") ?? [
            "source" => "",
            "cover" => "/images/no-album.png",
            "artist" => "",
            "album" => "",
            "title" => "",
        ];
    }
}
