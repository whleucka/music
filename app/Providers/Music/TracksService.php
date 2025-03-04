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

        $tracks = TrackMeta::where("title", "LIKE", "%$term%")
            ->orWhere("album", "LIKE", "%$term%")
            ->orWhere("artist", "LIKE", "%$term%")
            ->get() ?? [];

        return array_map(function($meta) {
            $track = Track::find($meta->track_id);
            return [
                "hash" => $track->hash,
                "title" => $meta->title,
                "artist" => $meta->artist,
                "album" => $meta->album,
                "playtime_string" => $meta->playtime_string,
            ];
        }, $tracks);
    }

    public function getTrackFromHash(string $hash): ?Track
    {
        return Track::where("hash", $hash)->get();
    }
}

