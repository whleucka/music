<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlaylistService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlaylistController extends Controller
{
    public function __construct(private PlaylistService $provider)
    {
    }

    // Playlist view
    #[Get("/playlist", "playlist.index")]
    public function index(): string
    {
        return $this->render("playlist/index.html.twig");
    }

    // Load the current playlist
    #[Get("/playlist/load", "playlist.load")]
    public function load(): string
    {
        return $this->render("playlist/load.html.twig", [
            "tracks" => $this->provider->getPlaylist(),
        ]);
    }

    // Generate a random playlist
    #[Get("/playlist/random", "playlist.random")]
    public function random(): void
    {
        $this->provider->randomPlaylist();
        trigger("playlist");
    }

    // Clear the current playlist
    #[Get("/playlist/clear", "playlist.clear")]
    public function clear(): void
    {
        $this->provider->clearPlaylist();
        trigger("playlist");
    }
}
