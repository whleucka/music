<?php

namespace App\Providers\Music;

class PlayerService
{
    public function __construct(private PlaylistService $playlist_provider) {}

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

    public function getPlayer(): array
    {
        return session()->get("player") ?? [
            "id" => "",
            "source" => "",
            "cover" => "/images/no-album.png",
            "artist" => "",
            "album" => "",
            "title" => "",
        ];
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

    public function setPlaylistTrack(int $index): void
    {
        $playlist = $this->playlist_provider->getPlaylist();
        $this->playlist_provider->setCurrentIndex($index);
        $track = $playlist[$index] ?? null;
        if ($track) {
            $this->setPlayer($track['hash'], "/tracks/stream/{$track['hash']}", $track['cover'], $track['artist'], $track['album'], $track['title']);
        }
    }

    public function getNextIndex(): ?int
    {
        $index = $this->playlist_provider->getCurrentIndex();
        $playlist = $this->playlist_provider->getPlaylist();
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
        $index = $this->playlist_provider->getCurrentIndex();
        $playlist = $this->playlist_provider->getPlaylist();
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
