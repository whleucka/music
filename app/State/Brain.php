<?php

namespace App\State;

class Brain
{
    private array $state;

    public function __construct()
    {
        $this->state = [
            "tracks" => container()->get(Tracks::class),
            "playlist" => container()->get(Playlist::class),
            "player" => container()->get(Player::class),
        ];
    }

    public function __get(string $name): mixed
    {
        return $this->state[$name];
    }
}
