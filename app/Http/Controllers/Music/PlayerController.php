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

    #[Get("/player", "player.index")]
    public function index(): string
    {
        return $this->render("player/index.html.twig", [
            "player" => $this->provider->getPlayer(),
        ]);
    }
}
