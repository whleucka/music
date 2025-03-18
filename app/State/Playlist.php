<?php

namespace App\State;

use Echo\Traits\State\SessionProperties;


class Playlist
{
    use SessionProperties;

    public string $state_name = "playlist";
    public array $default = [
        "id" => "",
        "index" => 0,
        "tracks" => [],
    ];
}
