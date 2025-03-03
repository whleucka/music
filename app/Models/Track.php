<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class Track extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('tracks', $id);
    }
}
