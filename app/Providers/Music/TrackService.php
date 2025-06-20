<?php

namespace App\Providers\Music;

use App\Models\Track;
use App\Models\TrackPlay;

class TrackService
{
    public function getSearchTerm(): ?string
    {
        return brain()->tracks->term;
    }

    public function setSearchTerm(string $term): void
    {
        brain()->tracks->term = trim($term);
    }

    public function clearSearchTerm(): void
    {
        brain()->tracks->term = null;
    }

    public function getSearchResultsFromDB(int $user_id): ?array
    {
        $term = $this->getSearchTerm();
        if (!$term) return [];
        if (preg_match('/(:artist)/', $term)) {
            $term = str_replace(":artist ", "", $term);
            return db()->fetchAll("SELECT tracks.hash, track_meta.*, 
                (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
                FROM tracks 
                INNER JOIN track_meta ON track_meta.track_id = tracks.id
                WHERE (artist = ?)
                ORDER BY album, CAST(track_number as UNSIGNED)", [$user_id, $term]) ?? [];
        } elseif (preg_match('/(:albumhash)/', $term)) {
            $term = str_replace(":albumhash ", "", $term);
            $track = $this->getTrackFromHash($term);
            if ($track) {
                $path_arr = explode("/", $track->pathname);
                array_pop($path_arr);
                $path = implode("/", $path_arr);
                return db()->fetchAll("SELECT tracks.hash, track_meta.*, 
                    (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
                    FROM tracks 
                    INNER JOIN track_meta ON track_meta.track_id = tracks.id
                    WHERE (pathname LIKE ?)
                    ORDER BY album, CAST(track_number as UNSIGNED)", [$user_id, "$path%"]) ?? [];
            }
        } elseif (preg_match('/(:album)/', $term)) {
            $term = str_replace(":album ", "", $term);
            return db()->fetchAll("SELECT tracks.hash, track_meta.*, 
                (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
                FROM tracks 
                INNER JOIN track_meta ON track_meta.track_id = tracks.id
                WHERE (album = ?)
                ORDER BY album, CAST(track_number as UNSIGNED)", [$user_id, $term]) ?? [];
        } elseif (preg_match('/(:genre)/', $term)) {
            $term = str_replace(":genre ", "", $term);
            return db()->fetchAll("SELECT tracks.hash, track_meta.*, 
                (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
                FROM tracks 
                INNER JOIN track_meta ON track_meta.track_id = tracks.id
                WHERE (genre LIKE ?)
                ORDER BY album, CAST(track_number as UNSIGNED)", [$user_id, "%$term%"]) ?? [];
        }

        return db()->fetchAll("SELECT tracks.hash, track_meta.*, 
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id
            WHERE (artist LIKE ?) OR (album LIKE ?) OR (title LIKE ?) OR (genre LIKE ?)
            ORDER BY album, CAST(track_number as UNSIGNED)", [$user_id, ...array_fill(0, 4, "%$term%")]) ?? [];
    }

    public function getTrackFromHash(string $hash): ?Track
    {
        return Track::where("hash", $hash)->get();
    }

    public function logPlay(int $user_id, int $track_id): void
    {
        TrackPlay::create([
            "user_id" => $user_id,
            "track_id" => $track_id,
        ]);
    }

    public function getRecentlyPlayedFromDB(int $user_id, int $limit = 10)
    {
        return db()->fetchAll("SELECT tracks.hash, track_meta.*,
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = track_play.track_id) as liked
            FROM track_play
            INNER JOIN tracks ON track_id = tracks.id
            INNER JOIN track_meta ON track_meta.track_id = tracks.id
            WHERE user_id = ?
            ORDER BY track_play.id DESC
            LIMIT $limit", [$user_id, $user_id]) ?? [];
    }

    public function getTopPlayedFromDB(int $user_id, int $limit = 10)
    {
        return db()->fetchAll("SELECT tracks.hash,
            ANY_VALUE(track_meta.cover) AS cover,
            ANY_VALUE(track_meta.album) AS album,
            ANY_VALUE(track_meta.title) AS title,
            ANY_VALUE(track_meta.genre) AS genre,
            ANY_VALUE(track_meta.year) AS year,
            ANY_VALUE(track_meta.track_number) AS track_number,
            ANY_VALUE(track_meta.playtime_string) AS playtime_string,
            ANY_VALUE(track_meta.bitrate) AS bitrate,
            ANY_VALUE(track_meta.mime_type) AS mime_type,
            COUNT(track_play.id) AS play_count,
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = track_play.track_id) as liked
            FROM track_play
            INNER JOIN tracks ON track_id = tracks.id
            INNER JOIN track_meta ON track_meta.track_id = tracks.id
            GROUP BY track_play.track_id
            ORDER BY count(track_play.track_id) DESC
            LIMIT $limit", [$user_id]) ?? [];
    }

    public function getUserTopPlayedFromDB(int $user_id, int $limit = 10)
    {
        return db()->fetchAll("SELECT tracks.hash,
            ANY_VALUE(track_meta.cover) AS cover,
            ANY_VALUE(track_meta.artist) AS artist,
            ANY_VALUE(track_meta.album) AS album,
            ANY_VALUE(track_meta.title) AS title,
            ANY_VALUE(track_meta.genre) AS genre,
            ANY_VALUE(track_meta.year) AS year,
            ANY_VALUE(track_meta.track_number) AS track_number,
            ANY_VALUE(track_meta.playtime_string) AS playtime_string,
            ANY_VALUE(track_meta.bitrate) AS bitrate,
            ANY_VALUE(track_meta.mime_type) AS mime_type,
            COUNT(track_play.id) AS play_count,
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = track_play.track_id) as liked
            FROM track_play
            INNER JOIN tracks ON track_id = tracks.id
            INNER JOIN track_meta ON track_meta.track_id = tracks.id
            WHERE user_id = ?
            GROUP BY track_play.track_id
            ORDER BY count(track_play.track_id) DESC
            LIMIT $limit", [$user_id, $user_id]) ?? [];
    }
}
