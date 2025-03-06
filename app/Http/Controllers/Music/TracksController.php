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
        if ($this->request->get->has('redirect')) {
            location("/tracks", select: "#view", target: "#view", swap: "outerHTML");
        }
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
            $playlist = $this->playlist_provider->getPlaylist();
            if ($playlist) {
                foreach ($playlist as $idx => $playlist_track) {
                    if ($playlist_track['hash'] === $hash) {
                        $this->playlist_provider->setCurrentIndex($idx);
                        break;
                    }
                }
            }
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
            $pathname = $track->meta()->mime_type !== 'audio/mpeg'
                ? $track->transcode()
                : $track->pathname;
            if (!$pathname) exit;
			header("Content-Type: audio/mpeg");
			header("Content-Length: " . filesize($pathname));
			header("Accept-Ranges: bytes");
			header("Content-Transfer-Encoding: binary");
			readfile($pathname);
			exit;
		}
    }

    // Display an album cover with specific dimensions
    #[Get("/tracks/cover/{hash}/{width}/{height}", "tracks.cover")]
    public function cover(string $hash, int $width, int $height): mixed
    {
        $track = $this->track_provider->getTrackFromHash($hash);
        if ($track) {
            try {
                // Set headers
                $expires = 60 * 60 * 24 * 30; // about a month
                header("Cache-Control: public, max-age={$expires}");
                header("Expires: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
                header("Access-Control-Allow-Origin: *");
                header("Content-Type: image/png");

                $cache_directory = "/tmp/";
                // Generate a unique cache filename based on the parameters.
                $dir_name = dirname($track->pathname);
                $cache_filename = md5($dir_name) . '.png';
                $cache_filepath = $cache_directory . $cache_filename;

                // Check if the cached image exists.
                if (file_exists($cache_filepath)) {
                    // Serve the cached image.
                    readfile($cache_filepath);
                    exit;
                }

                $storage_path = config("paths.covers");
                $cover = $track->meta()->cover;
                $filename = basename($cover);
                $image = $storage_path . $filename;

                if (file_exists($image) && $cover !== "/images/no-album.png") {
                    $imagick = new \imagick($image);
                    //crop and resize the image
                    $imagick->cropThumbnailImage($width, $height);
                    //remove the canvas
                    $imagick->setImagePage(0, 0, 0, 0);
                    $imagick->setImageFormat("png");
                    // Save the resized image to the cache directory.
                    $imagick->writeImage($cache_filepath);
                    echo $imagick->getImageBlob();
                    exit;
                } else {
                    // Serve the no album png
                    $no_album = config("paths.root") . "/public/images/no-album.png";
                    readfile($no_album);
                    exit;
                }
            } catch (\Exception $ex) {
                error_log("imagick error: check logs " . $ex->getMessage());
                exit;
            }
        }
    }
}
