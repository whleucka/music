<?php

namespace App\Http\Controllers\TOS;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class TOSController extends Controller
{
    #[Get("/terms-of-service", "tos.index")]
    public function index(): string
    {
        return $this->render("tos/index.html.twig", []);
    }
}

