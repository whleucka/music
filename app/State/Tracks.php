<?php

namespace App\State;

use Echo\Traits\State\SessionProperties;


class Tracks
{
    use SessionProperties;

    public string $state_name = "tracks";
    public array $default = [
        "term" => "",
        "results" => [],
    ];
}
