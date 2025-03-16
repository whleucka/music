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

    public function randomPlaylist(int $user_id, int $limit = 500): void
    {
        $tracks = db()->fetchAll("SELECT tracks.hash, track_meta.*, (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
            FROM tracks 
            INNER JOIN track_meta ON track_meta.track_id = tracks.id 
            ORDER BY RAND() 
            LIMIT $limit", [$user_id]);
        $this->setPlaylist($tracks);
    }

    public function getShuffle(): bool
    {
        return session()->get("shuffle") ?? false;
    }

    public function toggleShuffle(): void
    {
        $state = $this->getShuffle();
        session()->set("shuffle", !$state);
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
        $playlist = $this->getPlaylist();
        $this->setCurrentIndex($index);
        $track = $playlist[$index] ?? null;
        if ($track) {
            $this->setPlayer($track['hash'], "/tracks/stream/{$track['hash']}", $track['cover'], $track['artist'], $track['album'], $track['title']);
        }
    }

    public function getNextIndex(): ?int
    {
        $index = $this->getCurrentIndex();
        $playlist = $this->getPlaylist();
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
        $playlist = $this->getPlaylist();
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
