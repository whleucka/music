<?php

namespace App\Models;

use Echo\Framework\Database\Model;

class RadioStation extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('radio_stations', $id);
    }

    public function cover()
    {
        $fi = new FileInfo($this->cover);
        return $fi ? $fi->path : null;
    }
}
