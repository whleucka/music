<?php

namespace App\Providers\Music;

class TrackLikeService
{
    public function getUserTrackLike(int $user_id, int $track_id): ?array
    {
        return db()->fetch("SELECT *, 1 as liked
            FROM track_likes 
            WHERE user_id = ? AND track_id = ?", [$user_id, $track_id]);
    }

    public function getUserLikes(int $user_id): ?array
    {
        return db()->fetchAll("SELECT tracks.hash, track_meta.*, 1 as liked
            FROM track_likes 
            INNER JOIN tracks ON tracks.id = track_likes.track_id
            INNER JOIN track_meta ON track_meta.track_id= tracks.id
            WHERE user_id = ?
            ORDER BY track_meta.artist, track_meta.album, CAST(track_number as UNSIGNED)", [$user_id]);
    }

    public function likeTrack(int $user_id, int $track_id): void
    {
        db()->execute("INSERT IGNORE INTO track_likes 
            (user_id, track_id) VALUES (?,?)", [$user_id, $track_id]);
    }

    public function unlikeTrack(int $user_id, int $track_id): void
    {
        db()->execute("DELETE FROM track_likes 
            WHERE user_id = ? AND track_id = ?", [$user_id, $track_id]);
    }
}
