<?php

namespace App\Http\Controllers\Privacy;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class PrivacyPolicyController extends Controller
{
    #[Get("/privacy-policy", "privacy.index")]
    public function index(): string
    {
        return $this->render("privacy/index.html.twig", [
            "email" => config("company.email"),
            "name" => config("app.name"),
            "url" => config("app.url"),
        ]);
    }
}
