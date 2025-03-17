<?php

namespace App\Http\Controllers\Music;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class RadioController extends Controller
{
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
            "radio" => [],
        ]);
    }
}

