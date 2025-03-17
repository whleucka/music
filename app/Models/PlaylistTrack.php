<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class PlaylistTrack extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('playlist_tracks', $id);
    }

    public function playlist(): Playlist
    {
        return Playlist::find($this->playlist_id);
    }

    public function track(): Track
    {
        return Track::find($this->track_id);
    }
}

