<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlayerService;
use App\Providers\Music\PlaylistService;
use App\Providers\Music\TrackLikeService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlaylistController extends Controller
{
    public function __construct(
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
        $tracks = $this->track_like_provider->getUserLikes($this->user->id);
        return $this->render("playlist/load.html.twig", [
            "has_liked" => ($tracks),
            "tracks" => $this->playlist_provider->getPlaylist(),
            "id" => $this->player_provider->getPlayer()["id"],
        ]);
    }

    // Generate a random playlist
    #[Get("/playlist/random", "playlist.random", ["auth"])]
    public function random(): void
    {
        $this->playlist_provider->randomPlaylist();
        trigger("playlist");
    }

    // Generate a random playlist
    #[Get("/playlist/liked", "playlist.liked", ["auth"])]
    public function liked(): void
    {
        $tracks = $this->track_like_provider->getUserLikes($this->user->id);
        $this->playlist_provider->setPlaylist($tracks);
        trigger("playlist");
    }

    // Clear the current playlist
    #[Get("/playlist/clear", "playlist.clear", ["auth"])]
    public function clear(): void
    {
        $this->playlist_provider->clearPlaylist();
        trigger("playlist");
    }
}
