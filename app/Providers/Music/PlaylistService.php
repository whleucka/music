<?php

namespace App\Providers\Music;

class PlaylistService
{
    public function setPlaylist(array $tracks)
    {
        session()->set("playlist", $tracks);
        $this->setCurrentIndex(-1);
    }

    public function clearPlaylist()
    {
        session()->delete("playlist");
    }

    public function getPlaylist()
    {
        return session()->get("playlist");
    }

    public function getCurrentIndex()
    {
        $index = session()->get("playlist_index");
        if (is_null($index)) {
            $this->setCurrentIndex(0);
        }
        return $index ?? 0;
    }

    public function setCurrentIndex(int $index)
    {
        session()->set("playlist_index", $index);
    }

    public function randomPlaylist(int $limit = 500)
    {
        $tracks = db()->fetchAll("SELECT tracks.hash, track_meta.* 
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id 
            ORDER BY RAND() 
            LIMIT $limit");
        $this->setPlaylist($tracks);
    }
}

