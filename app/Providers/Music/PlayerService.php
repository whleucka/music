<?php

namespace App\Providers\Music;

class PlayerService
{
    public function setPlayer(
        string $id,
        string $source,
        string $cover,
        string $artist,
        string $album,
        string $title,
        string $type
    ): void 
    {
        $shuffle = brain()->player->shuffle;
        $player = [
            "id" => $id,
            "source" => $source,
            "cover" => $cover,
            "artist" => $artist,
            "album" => $album,
            "title" => $title,
            "shuffle" => $shuffle,
            "type" => $type,
        ];
        brain()->player->setState($player);
    }

    public function getPlayer(): array
    {
        return brain()->player->getState();
    }
}
