<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class TrackMeta extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('track_meta', $id);
    }
}
