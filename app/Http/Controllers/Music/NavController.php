<?php

namespace App\Http\Controllers\Music;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class NavController extends Controller
{
    // Render the navbar
    #[Get("/navbar", "navbar.index", ["auth"])]
    public function index(): string
    {
        return $this->render("navbar/index.html.twig");
    }
}
