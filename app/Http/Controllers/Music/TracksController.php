<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlayerService;
use App\Providers\Music\PlaylistService;
use App\Providers\Music\TrackService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class TracksController extends Controller
{
    public function __construct(
        private TrackService $track_provider,
        private PlayerService $player_provider,
        private PlaylistService $playlist_provider,
    ) {}

    // Default route
    #[Get("/", "app")]
    public function app(): void
    {
        redirect("/playlist");
    }

    // Tracks view
    #[Get("/tracks", "tracks.index", ["auth"])]
    public function index(): string
    {
        return $this->render("tracks/index.html.twig");
    }

    // Load the search control
    #[Get("/tracks/control", "tracks.control", ["auth"])]
    public function control(): string
    {
        return $this->render("tracks/control.html.twig", [
            "term" => $this->track_provider->getSearchTerm(),
        ]);
    }

    // Load the search results
    #[Get("/tracks/load", "tracks.load", ["auth"])]
    public function load(): string
    {
        return $this->render("tracks/load.html.twig", [
            "playlists" => $this->playlist_provider->getUserPlaylistsFromDB($this->user->id),
            "term" => $this->track_provider->getSearchTerm(),
            "tracks" => $this->track_provider->getSearchResultsFromDB($this->user->id),
            // "top_played" => $this->track_provider->getTopPlayedFromDB(),
        ]);
    }

    // Get most recent played tracks
    #[Get("/tracks/recently-played", "tracks.recently-played", ["auth"])]
    public function recently_played(): string
    {
        return $this->render("tracks/recently-played.html.twig", [
            "recently_played" => $this->track_provider->getRecentlyPlayedFromDB(),
        ]);
    }

    // Get top played user tracks
    #[Get("/tracks/top-played-user", "tracks.top-played-user", ["auth"])]
    public function top_played_user(): string
    {
        return $this->render("tracks/top-played-user.html.twig", [
            "top_played" => $this->track_provider->getUserTopPlayedFromDB($this->user->id),
        ]);
    }

    // Search for a track
    #[Get("/tracks/search", "tracks.search", ["auth"])]
    public function search(): string
    {
        $valid = $this->validate([
            "term" => ["required", "min_length:2"],
        ]);

        if ($valid) {
            $this->track_provider->setSearchTerm($valid->term);
        }

        trigger("tracks");
        if ($this->request->get->has('redirect')) {
            location("/tracks", select: "#view", target: "#view", swap: "outerHTML");
        }
        return $this->control();
    }

    // Clear the track search term and results
    #[Get("/tracks/clear", "tracks.clear", ["auth"])]
    public function clear(): string
    {
        $this->track_provider->clearSearchTerm();

        trigger("tracks");
        return $this->control();
    }

    // Set search results as playlist
    #[Get("/tracks/set-playlist", "tracks.set-playlist", ["auth"])]
    public function setPlaylist(): void
    {
        $tracks = $this->track_provider->getSearchResultsFromDB($this->user->id);
        $this->playlist_provider->setPlaylistTracks($tracks);
        location("/playlist", select: "#view", target: "#view", swap: "outerHTML");
    }

    // Set the player session and reload the player element
    #[Get("/tracks/play/{hash}", "tracks.play", ["auth"])]
    public function play(string $hash): void
    {
        $track = $this->track_provider->getTrackFromHash($hash);

        if ($track) {
            // Tracks from session, but queried from DB
            $tracks = $this->playlist_provider->getPlaylistTracks();
            if ($tracks) {
                foreach ($tracks as $index => $playlist_track) {
                    if ($playlist_track['hash'] === $hash) {
                        $this->playlist_provider->setPlaylistTrackIndex($index);
                        break;
                    }
                }
            }
            $meta = $track->meta();
            $this->playlist_provider->setPlayer($hash, "/tracks/stream/$hash", $meta->cover, $meta->artist, $meta->album, $meta->title);
            $this->track_provider->logPlay($this->user->id, $track->id);
            trigger("player, recently-played, top-played-user");
        }
    }

    // Stream a track for playback
    #[Get("/tracks/stream/{hash}", "tracks.stream", ["auth"])]
    public function stream(string $hash): void
    {
        $track = $this->track_provider->getTrackFromHash($hash);
        if ($track) {
            $track->stream();
        }
    }

    // Display an album cover with specific dimensions
    #[Get("/tracks/cover/{hash}/{width}/{height}", "tracks.cover", ["auth"])]
    public function cover(string $hash, int $width, int $height): void
    {
        $track = $this->track_provider->getTrackFromHash($hash);
        if ($track) {
            $track->renderCover($width, $height);
        }
    }
}
