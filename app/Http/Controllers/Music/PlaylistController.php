<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlayerService;
use App\Providers\Music\PlaylistService;
use App\Providers\Music\TrackLikeService;
use App\Providers\Music\TrackService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlaylistController extends Controller
{
    public function __construct(
        private TrackService $track_provider,
        private TrackLikeService $track_like_provider,
        private PlaylistService $playlist_provider, 
        private PlayerService $player_provider
    ) {}

    // Playlist view
    #[Get("/playlist", "playlist.index", ["auth"])]
    public function index(): string
    {
        return $this->render("playlist/index.html.twig");
    }

    // Load the current playlist
    #[Get("/playlist/load", "playlist.load", ["auth"])]
    public function load(): string
    {
        $tracks = $this->track_like_provider->getUserLikesFromDB($this->user->id);
        return $this->render("playlist/load.html.twig", [
            "has_liked" => ($tracks),
            "playlists" => $this->playlist_provider->getUserPlaylistsFromDB($this->user->id),
            "tracks" => $this->playlist_provider->getPlaylistTracks(),
            "id" => $this->player_provider->getPlayer()["id"],
        ]);
    }

    // Generate a random playlist
    #[Get("/playlist/random", "playlist.random", ["auth"])]
    public function random(): void
    {
        $this->playlist_provider->setRandomPlaylistFromDB($this->user->id);
        trigger("playlist");
    }

    // Generate a random playlist
    #[Get("/playlist/liked", "playlist.liked", ["auth"])]
    public function liked(): void
    {
        $tracks = $this->track_like_provider->getUserLikesFromDB($this->user->id);
        $this->playlist_provider->setPlaylistTracks($tracks);
        trigger("playlist");
    }

    // Clear the current playlist
    #[Get("/playlist/clear", "playlist.clear", ["auth"])]
    public function clear(): void
    {
        $this->playlist_provider->clearPlaylistTracks();
        trigger("playlist");
    }

    // Play the next track in the playlist
    #[Get("/playlist/next-track", "playlist.next-track", ["auth"])]
    public function nextTrack(): void
    {
        if ($this->playlist_provider->nextTrack()) {
            $track = $this->playlist_provider->getCurrentPlaylistTrack();
            $this->track_provider->logPlay($this->user->id, $track['id']);
            trigger("player, recently-played, top-played-user");
        }
    }

    // Play the previous track in the playlist
    #[Get("/playlist/prev-track", "playlist.prev-track", ["auth"])]
    public function prevTrack(): void
    {
        if ($this->playlist_provider->prevTrack()) {
            $track = $this->playlist_provider->getCurrentPlaylistTrack();
            $this->track_provider->logPlay($this->user->id, $track['id']);
            trigger("player, recently-played, top-played-user");
        }
    }

    // Play playlist
    #[Get("/playlist/play", "playlist.play", ["auth"])]
    public function play(): void
    {
        $this->playlist_provider->clearPlaylistTrackIndex();
        $this->nextTrack();
    }

    // Toggle the shuffle button
    #[Get("/playlist/shuffle/toggle", "playlist.shuffle-toggle", ["auth"])]
    public function shuffleToggle(): string
    {
        $state = $this->playlist_provider->toggleShuffle();
        return $this->render("player/shuffle.html.twig", [
            "state" => $state
        ]);
    }
}
