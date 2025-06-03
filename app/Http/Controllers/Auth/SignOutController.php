<?php

namespace App\Http\Controllers\Auth;

use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\Get;
use Echo\Framework\Session\Flash;

class SignOutController extends Controller
{
    #[Get("/sign-out", "auth.sign-out.index")]
    public function index(): void
    {
        session()->destroy();
        Flash::add("success", "You are now signed out");
        $path = uri("auth.sign-in.index");
        header("Location: $path");
    }
}
