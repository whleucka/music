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
            "playlists" => $this->provider->getUserPlaylists($this->user->id)
        ]);
    }

    // Play a user playlist
    #[Get("/playlists/{uuid}/play", "playlists.play", ["auth"])]
    public function playPlaylist($uuid): void
    {
        $this->provider->playPlaylist($this->user->id, $uuid);
        location("/playlist", select: "#view", target: "#view", swap: "outerHTML");
    }

    // Get a list of user playlists
    #[Get("/playlists/list/{hash}", "playlists.list", ["auth"])]
    public function list(string $hash): string
    {
        return $this->render("playlists/list.html.twig", [
            "hash" => $hash,
            "playlists" => $this->provider->getPlaylistListTrack($this->user->id, $hash),
        ]);
    }

    // Add track to user playlist
    #[Get("/playlists/{uuid}/{hash}", "playlist.list", ["auth"])]
    public function add(string $uuid, string $hash): void
    {
        $this->provider->toggleTrackPlaylist($this->user->id, $uuid, $hash);
    }

    #[Get("/playlists/create", "playlists.create", ["auth"])]
    public function create(): void
    {
        $valid = $this->validate([
            "name" => ["required", "min_length:2"]
        ]);

        if ($valid) {
            $this->provider->createPlaylist($this->user->id, $valid->name);
        }

        trigger("playlists");
    }

    #[Get("/playlists/delete/{uuid}", "playlists.delete", ["auth"])]
    public function delete(string $uuid): void
    {
        $playlist = $this->provider->getUserPlaylist($this->user->id, $uuid);
        if ($playlist) {
            $this->provider->deletePlaylist($playlist['id']);
            trigger("playlists");
        }
    }
}
