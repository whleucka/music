<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class Playlist extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('playlists', $id);
    }

    public function user(): User
    {
        return User::find($this->user_id);
    }
}

