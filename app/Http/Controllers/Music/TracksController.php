<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlayerService;
use App\Providers\Music\PlaylistService;
use App\Providers\Music\TracksService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class TracksController extends Controller
{
    public function __construct(
        private TracksService $track_provider, 
        private PlayerService $player_provider,
        private PlaylistService $playlist_provider,
    ) {}

    // Tracks view
    #[Get("/tracks", "tracks.index")]
    public function index(): string
    {
        return $this->render("tracks/index.html.twig");
    }

    // Load the search control
    #[Get("/tracks/control", "tracks.control")]
    public function control(): string
    {
        return $this->render("tracks/control.html.twig", [
            "term" => $this->track_provider->getSearchTerm(),
        ]);
    }

    // Load the search results
    #[Get("/tracks/results", "tracks.results")]
    public function results(): string
    {
        return $this->render("tracks/results.html.twig", [
            "term" => $this->track_provider->getSearchTerm(),
            "tracks" => $this->track_provider->getSearchResults(),
        ]);
    }

    // Search for a track
    #[Get("/tracks/search", "tracks.search")]
    public function search(): string
    {
        $valid = $this->validate([
            "term" => ["required", "min_length:2"],
        ]);

        if ($valid) {
            $this->track_provider->setSearchTerm($valid->term);
        }

        trigger("trackResults");
        return $this->control();
    }

    // Clear the track search term and results
    #[Get("/tracks/clear", "tracks.clear")]
    public function clear(): string
    {
        $this->track_provider->clearSearchTerm();

        trigger("trackResults");
        return $this->control();
    }

    // Set search results as playlist
    #[Get("/tracks/set-playlist", "tracks.set-playlist")]
    public function setPlaylist()
    {
        $tracks = $this->track_provider->getSearchResults();
        $this->playlist_provider->setPlaylist($tracks);
        location("/playlist", select: "#view", target: "#view", swap: "outerHTML");
    }


    // Set the player session and reload the player element
    #[Get("/tracks/play/{hash}", "tracks.play")]
    public function play(string $hash): void
    {
        $track = $this->track_provider->getTrackFromHash($hash);

        if ($track) {
            $meta = $track->meta();
            $this->player_provider->setPlayer($hash, "/tracks/stream/$hash", $meta->cover, $meta->artist, $meta->album, $meta->title);
            trigger("player");
        }
    }

    // Stream a track for playback
    #[Get("/tracks/stream/{hash}", "tracks.stream")]
    public function stream(string $hash)
    {
        $track = $this->track_provider->getTrackFromHash($hash);

        if ($track) {
			header("Content-Type: audio/mpeg");
			header("Content-Length: " . $track->size());
			header("Accept-Ranges: bytes");
			header("Content-Transfer-Encoding: binary");
			readfile($track->pathname);
			exit;
		}
    }
}
