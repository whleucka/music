<?php

namespace App\Providers\Music;

use App\Models\Playlist;

class PlaylistService
{
    public function setPlaylistTracks(array $tracks): void
    {
        session()->set("playlist", $tracks);
        $this->clearCurrentIndex(null);
    }

    public function setPlaylist(int $playlist_id): void
    {
        $tracks = $this->getPlaylistTracks($playlist_id);
        $this->setPlaylistTracks($tracks ?? []);
    }

    public function getPlaylists(int $user_id): ?array
    {
        return db()->fetchAll("SELECT *
            FROM playlists
            WHERE user_id = ?", [$user_id]);
    }

    public function getPlaylistTracks(int $playlist_id): ?array
    {
        return db()->fetchAll("SELECT tracks.hash, track_meta.*,
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
            FROM playlist_tracks 
            INNER JOIN tracks ON tracks.id = playlist_tracks.track_id
            INNER JOIN track_meta ON track_meta.track_id= tracks.id
            WHERE playlist_id = ?
            ORDER BY track_meta.artist, track_meta.album, CAST(track_number as UNSIGNED)", [$playlist_id]);
    }

    public function createPlaylist(int $user_id, string $name): Playlist|bool
    {
        return Playlist::create([
            "user_id" => $user_id,
            "name" => $name,
        ]);
    }

    public function deletePlaylist(int $playlist_id): void
    {
        $playlist = Playlist::find($playlist_id);
        if ($playlist) {
            $playlist->delete();
        }
    }

    public function clearPlaylist(): void
    {
        session()->delete("playlist");
    }

    public function getPlaylist(int $user_id, string $uuid): ?array
    {
        return db()->fetch("SELECT * 
            FROM playlists 
            WHERE user_id = ? AND uuid = ?", [$user_id, $uuid]);
    }

    public function getCurrentPlaylistTracks(): array
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

    public function randomPlaylist(int $user_id, int $limit = 500): void
    {
        $tracks = db()->fetchAll("SELECT tracks.hash, track_meta.*, 
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id 
            ORDER BY RAND() 
            LIMIT $limit", [$user_id]);
        $this->setPlaylistTracks($tracks);
    }

    public function getShuffle(): bool
    {
        return session()->get("shuffle") ?? false;
    }

    public function toggleShuffle(): bool
    {
        $state = $this->getShuffle();
        session()->set("shuffle", !$state);
        return !$state;
    }

    public function setPlayer(string $id, string $source, string $cover, string $artist, string $album, string $title): void
    {
        $player = [
            "id" => $id,
            "source" => $source,
            "cover" => $cover,
            "artist" => $artist,
            "album" => $album,
            "title" => $title,
        ];
        session()->set("player", $player);
    }

    public function setPlaylistTrack(int $index): void
    {
        $playlist = $this->getCurrentPlaylistTracks();
        $this->setCurrentIndex($index);
        $track = $playlist[$index] ?? null;
        if ($track) {
            $this->setPlayer($track['hash'], "/tracks/stream/{$track['hash']}", $track['cover'], $track['artist'], $track['album'], $track['title']);
        }
    }

    public function getNextIndex(): ?int
    {
        $index = $this->getCurrentIndex();
        $playlist = $this->getCurrentPlaylistTracks();
        if (!$playlist || count($playlist) <= 1) return null;
        $shuffle = $this->getShuffle();
        if (is_null($index) && !$shuffle) return 0;
        if ($shuffle) {
            $index = rand(0, count($playlist) - 1);
        } else {
            // Wrap around
            if ($index + 1 > count($playlist) - 1) $index = 0;
            else $index = $index + 1;
        }

        return $index % count($playlist);
    }

    public function getPrevIndex(): ?int
    {
        $index = $this->getCurrentIndex();
        $playlist = $this->getCurrentPlaylistTracks();
        if (!$playlist || count($playlist) <= 1) return null;
        $shuffle = $this->getShuffle();
        if (is_null($index) && !$shuffle) return 0;

        if ($shuffle) {
            $index = rand(0, count($playlist) - 1);
        } else {
            // Wrap around
            if ($index - 1 < 0) $index = count($playlist) - 1;
            else $index = $index - 1;
        }

        return $index % count($playlist);
    }

    public function nextTrack(): bool
    {
        $next_index = $this->getNextIndex();
        if (!is_null($next_index)) {
            $this->setPlaylistTrack($next_index);
            return true;
        }
        return false;
    }

    public function prevTrack(): bool
    {
        $prev_index = $this->getPrevIndex();
        if (!is_null($prev_index)) {
            $this->setPlaylistTrack($prev_index);
            return true;
        }
        return false;
    }
}
