<?php

namespace App\Http\Controllers\Auth;

use App\Providers\Auth\SignInService;
use Echo\Framework\Http\Controller;
use Echo\Framework\Routing\Route\{Get, Post};
use Echo\Framework\Session\Flash;

class SignInController extends Controller
{
    public function __construct(private SignInService $provider)
    {
    }

    #[Get("/sign-in", "auth.sign-in.index")]
    public function index(): string
    {
        return $this->render("auth/sign-in/index.html.twig");
    }

    #[Post("/sign-in", "auth.sign-in.post", ["max_requests" => 20])]
    public function post(): string
    {
        $valid = $this->validate([
            "email" => ["required", "email"],
            "password" => ["required"],
        ]);
        if ($valid) {
            $success = $this->provider->signIn($valid->email, $valid->password);
            if ($success) {
                $path = config("security.authenticated_route");
                header("HX-Redirect: $path");
            } else {
                Flash::add("warning", "Invalid email and/or password");
            }
        }
        return $this->index();
    }
}
