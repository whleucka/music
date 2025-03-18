<?php

namespace App\Providers\Music;

class PlayerService
{
    public function getPlayer(): array
    {
        return brain()->player->getState();
    }
}
