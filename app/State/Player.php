<?php

namespace App\State;

use Echo\Traits\State\SessionProperties;


class Player
{
    use SessionProperties;

    public string $state_name = "player";
    public array $default = [
        "id" => "",
        "source" => "",
        "cover" => "/images/no-album.png",
        "artist" => "",
        "album" => "",
        "title" => "",
        "shuffle" => false,
    ];

}
