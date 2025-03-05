<?php

namespace App\Providers\Music;

class PlayerService
{
    private PlaylistService $playlist_provider;

    public function __construct()
    {
        $this->playlist_provider = new PlaylistService;
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

    public function setPlaylistTrack(int $index)
    {
        $playlist = $this->playlist_provider->getPlaylist();
        $this->playlist_provider->setCurrentIndex($index);
        $track = $playlist[$index] ?? null;
        if ($track) {
            $this->setPlayer($track['hash'], "/tracks/stream/{$track['hash']}", $track['cover'], $track['artist'], $track['album'], $track['title']);
        }
    }

    public function nextTrack()
    {
        $index = $this->playlist_provider->getCurrentIndex();
        $playlist = $this->playlist_provider->getPlaylist();
        if (!$playlist) return;
        if ($index + 1 > count($playlist) - 1) $index = -1;
        $next_index = $index + 1 % count($playlist);
        $this->setPlaylistTrack($next_index);
    }

    public function prevTrack()
    {
        $index = $this->playlist_provider->getCurrentIndex();
        $playlist = $this->playlist_provider->getPlaylist();
        if (!$playlist) return;
        if ($index - 1 < 0) $index = count($playlist);
        $prev_index = $index - 1 % count($playlist);
        $this->setPlaylistTrack($prev_index);
    }
}
