<?php

namespace App\Providers\Music;

use App\Models\Track;

class TrackService
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
            WHERE (artist LIKE ?) OR (album LIKE ?) OR (title LIKE ?) OR (genre LIKE ?) OR (pathname LIKE ?)
            ORDER BY album, CAST(track_number as UNSIGNED)", array_fill(0, 5, "%$term%")) ?? [];
    }

    public function getTrackFromHash(string $hash): ?Track
    {
        return Track::where("hash", $hash)->get();
    }
}

