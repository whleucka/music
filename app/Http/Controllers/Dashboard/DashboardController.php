<?php

namespace App\Http\Controllers\Dashboard;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;

class DashboardController extends Controller
{
    #[Get("/dashboard", "dashboard.index", ["auth"])] 
    public function index(): string
    {
        return $this->render("dashboard/index.html.twig", [
            "first_name" => $this->user->first_name
        ]);
    }
}
