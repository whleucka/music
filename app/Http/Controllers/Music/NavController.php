<?php

namespace App\Http\Controllers\Music;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class NavController extends Controller
{
    #[Get("/navbar", "navbar.index")]
    public function index(): string
    {
        return $this->render("navbar/index.html.twig");
    }
}
