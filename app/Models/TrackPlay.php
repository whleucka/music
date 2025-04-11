<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class TrackPlay extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('track_play', $id);
    }

    public function user(): User
    {
        return User::find($this->user_id);
    }

    public function track(): Track
    {
        return Track::find($this->track_id);
    }
}
