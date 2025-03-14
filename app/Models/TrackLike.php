<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class TrackLike extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('track_likes', $id);
    }
}
