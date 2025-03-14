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
    #[Get("/player", "player.index", ["auth"])]
    public function index(): string
    {
        return $this->render("player/index.html.twig", [
            "player" => $this->provider->getPlayer(),
            "url" => config("app.url"),
        ]);
    }

    // Play the next track in the playlist
    #[Get("/player/next-track", "player.next-track", ["auth"])]
    public function nextTrack(): void
    {
        if ($this->provider->nextTrack()) {
            trigger("player");
        }
    }

    // Play the previous track in the playlist
    #[Get("/player/prev-track", "player.prev-track", ["auth"])]
    public function prevTrack(): void
    {
        if ($this->provider->prevTrack()) {
            trigger("player");
        }
    }

    // Get the shuffle button
    #[Get("/player/shuffle", "player.shuffle", ["auth"])]
    public function shuffle(): string
    {
        $state = $this->provider->getShuffle();
        return $this->render("player/shuffle.html.twig", [
            "state" => $state
        ]);
    }

    // Toggle the shuffle button
    #[Get("/player/shuffle/toggle", "player.shuffle-toggle", ["auth"])]
    public function shuffleToggle(): string
    {
        $this->provider->toggleShuffle();
        return $this->shuffle();
    }
}
