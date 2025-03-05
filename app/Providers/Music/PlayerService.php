<?php

namespace App\Providers\Music;

class PlayerService
{
    public function setPlayer(string $id, string $source, string $cover, string $artist, string $album, string $title): void
    {
        $player = [
            "id" => $id,
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
            "id" => "",
            "source" => "",
            "cover" => "/images/no-album.png",
            "artist" => "",
            "album" => "",
            "title" => "",
        ];
    }
}
