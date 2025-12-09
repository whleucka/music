<?php

namespace App\Http\Controllers\Music;

use App\Models\FileInfo;
use App\Providers\Music\PlayerService;
use App\Providers\Music\RadioService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class RadioController extends Controller
{
    public function __construct(
        private RadioService $radio_provider,
        private PlayerService $player_provider
    )
    {}

    // Render podcasts
    #[Get("/radio", "radio.index", ["auth"])]
    public function index(): string
    {
        return $this->render("radio/index.html.twig");
    }

    // Load the radio stations
    #[Get("/radio/load", "radio.load", ["auth"])]
    public function load(): string
    {
        return $this->render("radio/load.html.twig", [
            "stations" => $this->radio_provider->getRadioStationsFromDB(),
            "id" => $this->player_provider->getPlayer()["id"],
        ]);
    }

    #[Get("/radio/play/{hash}", "radio.play", ["auth"])]
    public function play(string $hash): void
    {
        $station = $this->radio_provider->getStationFromHash($hash);

        if ($station) {
            $fi = new FileInfo($station->cover);
            $this->radio_provider->setPlayer($hash, '/radio/stream/'.$station->hash, $fi->path, $station->city, $station->province, $station->country, $station->name);
            $this->hxTrigger("player");
        }
    }

    // Display an album cover with specific dimensions
    #[Get("/radio/cover/{hash}/{width}/{height}", "radio.cover", ["auth"])]
    public function cover(string $hash, int $width, int $height): void
    {
        $station = $this->radio_provider->getStationFromHash($hash);
        if ($station) {
            $station->renderCover($width, $height);
        }
    }

    // Stream a station for playback
    #[Get("/radio/stream/{hash}", "radio.stream", ["auth"])]
    public function stream(string $hash): void
    {
        $station = $this->radio_provider->getStationFromHash($hash);
        if ($station) {
            $station->stream();
        }
    }
}

