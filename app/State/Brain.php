<?php

namespace App\State;

class Brain
{
    public function __construct(
        public Tracks $tracks,
        public Playlist $playlist,
        public Player $player,
        public Radio $radio,
    ) {}
}
