<?php

namespace App\State;

use Echo\Traits\State\SessionProperties;


class Podcast
{
    use SessionProperties;

    public string $state_name = "podcast";
    public array $default = [
        "id" => "",
    ];
}
