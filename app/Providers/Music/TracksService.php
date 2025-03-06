<?php

namespace App\Providers\Music;

use App\Models\Track;
use App\Models\TrackMeta;

class TracksService
{
    public function getSearchTerm(): ?string
    {
        return session()->get("tracks_search");
    }

    public function setSearchTerm(string $term): void
    {
        session()->set("tracks_search", $term);
    }

    public function clearSearchTerm(): void
    {
        session()->delete("tracks_search");
    }

    public function getSearchResults(): array
    {
        $term = $this->getSearchTerm();
        if (!$term) return [];
        return db()->fetchAll("SELECT tracks.hash, track_meta.* 
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id
            WHERE (artist LIKE ?) OR (album LIKE ?) OR (title LIKE ?)
            ORDER BY album, track_number", array_fill(0, 3, "%$term%")) ?? [];
    }

    public function getTrackFromHash(string $hash): ?Track
    {
        return Track::where("hash", $hash)->get();
    }
}

