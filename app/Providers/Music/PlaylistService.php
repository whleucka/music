<?php

namespace App\Providers\Music;

use App\Models\Playlist;

class PlaylistService
{
    public function setPlaylist(int $playlist_id): void
    {
        $tracks = $this->getPlaylistByID($playlist_id);
        $this->setPlaylistTracks($tracks ?? []);
    }

    public function getUserPlaylists(int $user_id): ?array
    {
        return db()->fetchAll("SELECT *
            FROM playlists
            WHERE user_id = ?", [$user_id]);
    }

    public function getPlaylistByID(int $playlist_id): ?array
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

    public function getUserPlaylist(int $user_id, string $uuid): ?array
    {
        return db()->fetch("SELECT * 
            FROM playlists 
            WHERE user_id = ? AND uuid = ?", [$user_id, $uuid]);
    }

    public function getPlaylistTracks(): array
    {
        return brain()->playlist->tracks;
    }

    public function clearPlaylistTracks(): void
    {
        brain()->playlist->tracks = [];
    }

    public function setPlaylistTracks(array $tracks): void
    {
        brain()->playlist->tracks = $tracks;
    }

    public function getPlaylistTrackIndex(): ?int
    {
        return brain()->playlist->track_index;
    }

    public function setPlaylistTrackIndex(int $index): void
    {
        brain()->playlist->track_index = $index;
    }

    public function clearPlaylistTrackIndex(): void
    {
        brain()->playlist->track_index = null;
    }

    public function setRandomPlaylist(int $user_id, int $limit = 500): void
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
        return brain()->player->shuffle ?? false;
    }

    public function toggleShuffle(): bool
    {
        $shuffle = !$this->getShuffle();
        brain()->player->shuffle = $shuffle;
        return $shuffle;
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
            "shuffle" => $this->getShuffle(),
        ];
        brain()->player->setState($player);
    }

    public function setPlaylistTrack(int $index): void
    {
        $playlist = $this->getPlaylistTracks();
        $this->setPlaylistTrackIndex($index);
        $track = $playlist[$index] ?? null;
        if ($track) {
            $this->setPlayer($track['hash'], "/tracks/stream/{$track['hash']}", $track['cover'], $track['artist'], $track['album'], $track['title']);
        }
    }

    public function getNextIndex(): ?int
    {
        $index = $this->getPlaylistTrackIndex();
        $playlist = $this->getPlaylistTracks();
        $shuffle = $this->getShuffle();

        if (!$playlist || count($playlist) <= 1) return null;
        if (is_null($index) && !$shuffle) return 0;

        $index = $shuffle
            ? rand(0, count($playlist) - 1)
            : $index + 1;

        return $index % count($playlist);
    }

    public function getPrevIndex(): ?int
    {
        $index = $this->getPlaylistTrackIndex();
        $playlist = $this->getPlaylistTracks();
        $shuffle = $this->getShuffle();

        if (!$playlist || count($playlist) <= 1) return null;
        if (is_null($index) && !$shuffle) return 0;

        $index = $shuffle
            ? rand(0, count($playlist) - 1)
            : $index - 1;

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
