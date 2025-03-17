<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlaylistService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlaylistsController extends Controller
{
    public function __construct(private PlaylistService $provider)
    {
    }
    // Render user playlists
    #[Get("/playlists", "playlists.index", ["auth"])]
    public function index(): string
    {
        return $this->render("playlists/index.html.twig");
    }

    // Load the user playlists
    #[Get("/playlists/load", "playlists.load", ["auth"])]
    public function load(): string
    {
        return $this->render("playlists/load.html.twig", [
            "playlists" => $this->provider->getPlaylists($this->user->id)
        ]);
    }
}
