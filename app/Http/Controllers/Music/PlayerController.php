<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PlayerService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PlayerController extends Controller
{
    public function __construct(private PlayerService $provider)
    {
    }

    // Player view
    #[Get("/player", "player.index")]
    public function index(): string
    {
        return $this->render("player/index.html.twig", [
            "player" => $this->provider->getPlayer(),
        ]);
    }

    // Play the next track in the playlist
    #[Get("/player/next-track", "player.next-track")]
    public function nextTrack()
    {
        $this->provider->nextTrack();
        trigger("player");
    }

    // Play the previous track in the playlist
    #[Get("/player/prev-track", "player.prev-track")]
    public function prevTrack()
    {
        $this->provider->prevTrack();
        trigger("player");
    }
}
