<?php

namespace App\Providers\Music;

use App\Models\Playlist;
use App\Models\PlaylistTrack;
use App\Models\Track;

class PlaylistService
{
    public function setPlaylist(int $user_id, int $playlist_id): void
    {
        $tracks = $this->getPlaylistFromDB($user_id, $playlist_id);
        $this->setPlaylistTracks($tracks ?? []);
    }

    public function getUserPlaylistsFromDB(int $user_id): ?array
    {
        return db()->fetchAll("SELECT *
            FROM playlists
            WHERE user_id = ?", [$user_id]);
    }

    public function getPlaylistListFromDB(int $user_id, string $hash): ?array
    {
        $track = Track::where("hash", $hash)->get();
        return db()->fetchAll("SELECT *, (SELECT 1 
                FROM playlist_tracks 
                WHERE playlist_id = playlists.id AND track_id = ?) as has_track
            FROM playlists
            WHERE user_id = ?", [$track->id, $user_id]);
    }

    public function toggleTrackPlaylist(int $playlist_id, int $track_id): void
    {
        $playlist_track = $this->getPlaylistTrack($playlist_id, $track_id);

        if ($playlist_track) {
            // Exists, so delete it
            $playlist_track->delete();
        } else {
            $this->createPlaylistTrack($playlist_id, $track_id);
        }
    }

    public function createPlaylistTrack(int $playlist_id, int $track_id)
    {
        // Add track to the playlist
        PlaylistTrack::create([
            "playlist_id" => $playlist_id,
            "track_id" => $track_id,
        ]);
    }

    public function getPlaylistTrack(int $playlist_id, int $track_id)
    {

        return PlaylistTrack::where("playlist_id", $playlist_id)
            ->andWhere("track_id", $track_id)->get();
    }

    public function addTracksToPlaylist(int $user_id, ?array $tracks, string $uuid)
    {
        $playlist = $this->getPlaylistByUUID($user_id, $uuid);

        if (!$playlist) return;

        foreach ($tracks as $track) {
            $playlist_track = $this->getPlaylistTrack($playlist->id, $track['id']);
            if (!$playlist_track) {
                $this->createPlaylistTrack($playlist->id, $track['id']);
            }
        }
    }

    public function getPlaylistByUUID(int $user_id, string $uuid)
    {
        return Playlist::where("uuid", $uuid)->andWhere("user_id", $user_id)->get();
    }

    public function playPlaylist(int $user_id, string $uuid): void
    {
        $playlist = $this->getPlaylistByUUID($user_id, $uuid);

        if (!$playlist) return;

        $tracks = $this->getPlaylistFromDB($user_id, $playlist->id);
        $this->setPlaylistTracks($tracks);
    }

    public function getPlaylistFromDB(int $user_id, int $playlist_id): ?array
    {
        return db()->fetchAll("SELECT tracks.hash, track_meta.*,
            (SELECT 1 FROM track_likes WHERE user_id = ? AND track_id = tracks.id) as liked
            FROM playlist_tracks 
            INNER JOIN tracks ON tracks.id = playlist_tracks.track_id
            INNER JOIN track_meta ON track_meta.track_id= tracks.id
            WHERE playlist_id = ?
            ORDER BY track_meta.artist, track_meta.album, CAST(track_number as UNSIGNED)", [$user_id, $playlist_id]);
    }

    public function createPlaylist(int $user_id, string $name): Playlist|bool
    {
        return Playlist::create([
            "user_id" => $user_id,
            "name" => $name,
        ]);
    }

    public function deletePlaylist(int $user_id, int $playlist_id): void
    {
        $playlist = Playlist::where("id", $playlist_id)
            ->andWhere("user_id", $user_id)->get();
        if ($playlist) {
            $playlist->delete();
        }
    }

    public function getUserPlaylistFromDB(int $user_id, string $uuid): ?array
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

    public function setRandomPlaylistFromDB(int $user_id, int $limit = 500): void
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

    public function getCurrentPlaylistTrack()
    {
        $index = $this->getPlaylistTrackIndex();
        $playlist = $this->getPlaylistTracks();
        return $playlist[$index] ?? null;
    }

    private function getRandomIndexExcluding(int $count, ?int $exclude): int
    {
        if ($count <= 1) return 0;

        $rand = rand(0, $count - 1);
        while ($exclude !== null && $rand === $exclude) {
            $rand = rand(0, $count - 1);
        }
        return $rand;
    }

    public function getNextIndex(): ?int
    {
        $index = $this->getPlaylistTrackIndex();
        $playlist = $this->getPlaylistTracks();
        $shuffle = $this->getShuffle();

        if (!$playlist || count($playlist) <= 1) return null;
        if (is_null($index) && !$shuffle) return 0;

        $count = count($playlist);
        $new_index = $shuffle
            ? $this->getRandomIndexExcluding($count, $index)
            : $index + 1;

        return $new_index % $count;
    }

    public function getPrevIndex(): ?int
    {
        $index = $this->getPlaylistTrackIndex();
        $playlist = $this->getPlaylistTracks();
        $shuffle = $this->getShuffle();

        if (!$playlist || count($playlist) <= 1) return null;
        if (is_null($index) && !$shuffle) return 0;

        $count = count($playlist);
        $new_index = $shuffle
            ? $this->getRandomIndexExcluding($count, $index)
            : $index - 1;

        return $new_index % $count;
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
