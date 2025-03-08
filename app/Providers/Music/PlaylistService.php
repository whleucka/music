<?php

namespace App\Providers\Music;

class PlaylistService
{
    public function setPlaylist(array $tracks): void
    {
        session()->set("playlist", $tracks);
        $this->clearCurrentIndex(null);
    }

    public function clearPlaylist(): void
    {
        session()->delete("playlist");
    }

    public function getPlaylist(): array
    {
        return session()->get("playlist") ?? [];
    }

    public function getCurrentIndex(): ?int
    {
        return session()->get("playlist_index");
    }

    public function setCurrentIndex(int $index): void
    {
        session()->set("playlist_index", $index);
    }

    public function clearCurrentIndex(): void
    {
        session()->delete("playlist_index");
    }

    public function randomPlaylist(int $limit = 500): void
    {
        $tracks = db()->fetchAll("SELECT tracks.hash, track_meta.* 
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id 
            ORDER BY RAND() 
            LIMIT $limit");
        $this->setPlaylist($tracks);
    }
}
