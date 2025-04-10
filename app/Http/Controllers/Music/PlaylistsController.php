<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlaylistService;
use App\Providers\Music\TrackService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlaylistsController extends Controller
{
    public function __construct(
        private PlaylistService $playlist_provider,
        private TrackService $track_provider
    ) {}

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
            "playlists" => $this->playlist_provider->getUserPlaylists($this->user->id)
        ]);
    }

    // Play a user playlist
    #[Get("/playlists/play/{uuid}", "playlists.play", ["auth"])]
    public function playPlaylist(string $uuid): void
    {
        $this->playlist_provider->playPlaylist($this->user->id, $uuid);
        location("/playlist", select: "#view", target: "#view", swap: "outerHTML");
    }

    // Get a list of user playlists
    #[Get("/playlists/list/{hash}", "playlists.list", ["auth"])]
    public function list(string $hash): string
    {
        return $this->render("playlists/list.html.twig", [
            "hash" => $hash,
            "playlists" => $this->playlist_provider->getPlaylistListTrack($this->user->id, $hash),
        ]);
    }

    // Add track to user playlist
    #[Get("/playlists/add-track/{uuid}/{hash}", "playlist.add-track", ["auth"])]
    public function add(string $uuid, string $hash): void
    {
        $this->playlist_provider->toggleTrackPlaylist($this->user->id, $uuid, $hash);
    }

    // Add search to user playlist
    #[Get("/playlists/add-tracks/{uuid}", "playlist.add-tracks", ["auth"])]
    public function add_tracks(string $uuid): void
    {
        $tracks = $this->track_provider->getSearchResults($this->user->id);
        $this->playlist_provider->addTracksToPlaylist($this->user->id, $tracks, $uuid);
    }

    // Create a new playlist
    #[Get("/playlists/create", "playlists.create", ["auth"])]
    public function create(): void
    {
        $valid = $this->validate([
            "name" => ["required", "min_length:2"]
        ]);

        if ($valid) {
            $this->playlist_provider->createPlaylist($this->user->id, $valid->name);
        }

        trigger("playlists");
    }

    // Delete a playlist
    #[Get("/playlists/delete/{uuid}", "playlists.delete", ["auth"])]
    public function delete(string $uuid)
    {
        $playlist = $this->playlist_provider->getUserPlaylist($this->user->id, $uuid);
        if ($playlist) {
            $this->playlist_provider->deletePlaylist($playlist['id']);
            trigger("playlists");
        }
    }
}
