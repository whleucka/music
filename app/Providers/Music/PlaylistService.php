<?php

namespace App\Providers\Music;

class PlaylistService
{
    public function setPlaylist(array $tracks)
    {
        session()->set("playlist", $tracks);
    }

    public function getPlaylist()
    {
        return session()->get("playlist");
    }
}

