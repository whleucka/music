<?php

namespace App\Http\Controllers\Music;

use App\Providers\Music\PodcastService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PodcastsController extends Controller
{
    public function __construct(private PodcastService $podcast_provider)
    {
        
    }

    // Render podcasts
    #[Get("/podcasts", "podcasts.index", ["auth"])]
    public function index(): string
    {
        return $this->render("podcasts/index.html.twig");
    }

    // Load the podcasts
    #[Get("/podcasts/load", "podcasts.load", ["auth"])]
    public function load(): string
    {
        return $this->render("podcasts/load.html.twig", [
            "podcasts" => [],
        ]);
    }
}
