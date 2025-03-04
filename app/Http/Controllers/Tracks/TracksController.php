<?php

namespace App\Http\Controllers\Tracks;

use App\Providers\Music\TracksService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class TracksController extends Controller
{
    public function __construct(private TracksService $provider)
    {
    }

    #[Get("/tracks", "tracks.index")]
    public function index(): string
    {
        return $this->render("tracks/index.html.twig");
    }

    #[Get("/tracks/control", "tracks.control")]
    public function control(): string
    {
        return $this->render("tracks/control.html.twig", [
            "term" => $this->provider->getSearchTerm(),
        ]);
    }

    #[Get("/tracks/search", "tracks.search")]
    public function search(): string
    {
        $valid = $this->validate([
            "term" => ["required", "min_length:2"],
        ]);

        if ($valid) {
            $this->provider->setSearchTerm($valid->term);
        }

        return $this->control();
    }

    #[Get("/tracks/clear", "tracks.clear")]
    public function clear()
    {
        $this->provider->clearSearchTerm();
        return $this->control();
    }
}
