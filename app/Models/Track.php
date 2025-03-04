<?php

namespace App\Models;

use Echo\Framework\Database\Model;
use getid3_lib;
use getID3;

class Track extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('tracks', $id);
    }

    public function meta(): ?TrackMeta
    {
        return TrackMeta::where("track_id", $this->id)->get();
    }

    public function size(): int|false
    {
        return filesize($this->pathname);
    }

    public function getTags(): array
    {
        $getID3 = new getID3;
        $tags = $getID3->analyze($this->pathname);
        getid3_lib::CopyTagsToComments($tags);
        return $tags;
    }
}
