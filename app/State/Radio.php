<?php

namespace App\State;

use Echo\Traits\State\SessionProperties;


class Radio
{
    use SessionProperties;

    public string $state_name = "radio";
    public array $default = [
        "id" => "",
        "station_index" => 0,
        "stations" => [],
    ];
}
